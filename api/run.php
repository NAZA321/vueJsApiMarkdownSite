<?php
/**
 *
 */
header("Content-type: text/html; charset=utf-8");
//呼び出された際にDBファイルが存在するか確認
$dbFile = "sqlite.db";

$today = date("Ymd");
$fileHandle = fopen( "./data.txt", "r");
$txt = fgets($fileHandle);

// text確認後バックアップサクセスるかどうか調べる
if ($today != $txt) {
  $flg = copy($dbFile, './bk/' . $dbFile . '.' .$today);
  file_put_contents('./data.txt', $today);

    // 指定の拡張子のファイルを取得
  $log_files  = glob('./bk/*');

  // この日付以前のファイルを削除する(これは2ヶ月指定)
  $target_day = strtotime(date('YmdHis') . '-1 week');

  foreach ($log_files as $_log_files) {
    // ファイルの最終更新日を取得
    $m_date = filemtime($_log_files);

    // 最終更新日が指定日より前であれば削除
    if (strtotime(date('YmdHis', $m_date)) < $target_day) {
        unlink($_log_files);
    }
  }
}
fclose($fileHandle);

$api = new Api($dbFile);

$api->connectPdo();

if ($api->getMethot()) {
  $data = $api->createDataFunction();
  //$api->insertTestData();
  //$api->insertTestData2();
  //$data = $api->getMemoTitle();
  // GET であれば
} else {
  // POSTの場合
  // POSTされたJSON文字列を取り出し
  $postJson = file_get_contents("php://input");

  // JSON文字列をobjectに変換
  //   ⇒ 第2引数をtrueにしないとハマるので注意
  $contents = json_decode($postJson, true);
  // メモタイトルすべて取得
  if (isset($contents['type']) && $contents['type'] == "1") {
    $data = $api->selectMemoTitle();
  } else if (isset($contents['type']) && $contents['type'] == "content") {
    $api->insertMemoContents($contents['oya_id'], $contents['title'], $contents['source']);
    $data = [
      'status' => '200',
      'comments' => '登録成功',
    ];
  } else if (isset($contents['type']) && $contents['type'] == "contentAll") {
    $api->insertMemoContentsAll($contents['oya_title'], $contents['title'], $contents['source']);
    $data = [
      'status' => '200',
      'comments' => '登録成功',
    ];
  } else if (isset($contents['type']) && $contents['type'] == "update") {
    $api->updateContent($contents['id'], $contents['oya_id'], $contents['title'], $contents['source']);
    $data = [
      'status' => '200',
      'comments' => '登録成功',
    ];
  } else if (isset($contents['type']) && $contents['type'] == "delete") {
    $api->deleteMemoContents($contents['content_id'], $contents['main_id']);
    $data = [
      'status' => '200',
      'comments' => '削除成功',
    ];
  } else {
    $infoData = $api->getMemoContentInfo($contents['content_id'], $contents['main_id']);
    foreach ($infoData as $key => $value) {
      if ($key == 'title') {
        $data[$key] = htmlspecialchars_decode($value);
      } else if ($key == 'content') {
        $data[$key] = htmlspecialchars_decode($value);
      } else {
        $data[$key] = $value;
      }
    }
  }
}

// これが無いと外部サーバーからAPIを利用できない
header("Access-Control-Allow-Origin: *");
$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo $json;

class Api
{
  private $dbFile = "";
  private $methot = true;
  private $pdo = null;
  function __construct($fileName)
  {
    $this->dbFile = $fileName;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $this->methot = false;
    }
  }

  public function getMethot()
  {
    return $this->methot;
  }

  public function connectPdo()
  {
    try {
      // 接続
      $pdo = new PDO('sqlite:' . $this->dbFile);

      // SQL実行時にもエラーの代わりに例外を投げるように設定
      // (毎回if文を書く必要がなくなる)
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // デフォルトのフェッチモードを連想配列形式に設定
      // (毎回PDO::FETCH_ASSOCを指定する必要が無くなる)
      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
    $this->pdo = $pdo;
  }

  private function getMemoTitle()
  {
    $sql =<<<SQL
SELECT
  id,
  title
FROM
  memo_title
SQL;

    try {
      $res = $this->pdo->query($sql);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
    return $res->fetchAll();
  }

  public function selectMemoTitle()
  {
    $array = [];
    $data = $this->getMemoTitle();
    $array[] = [
      'id' => 0,
      'title' => '新規作成',
    ];
    foreach ($data as $key => $value) {
      $array[] = [
        'id' => $value['id'],
        'title' => $value['title'],
      ];
    }

    return $array;
  }

  private function getMemoContents()
  {
    $sql =<<<SQL
SELECT
  id,
  main_id,
  title
FROM
  memo_contents
SQL;

    try {
      $res = $this->pdo->query($sql);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
    return $res->fetchAll();
  }

  public function getMemoContentInfo($id, $mainId)
  {
    $sql =<<<SQL
SELECT
  title,
  content
FROM
  memo_contents
WHERE
  id = :id
AND
  main_id = :main_id
SQL;

    try {
      $stmt = $this->pdo->prepare($sql);

      // (4) 登録するデータをセット
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->bindParam(':main_id', $mainId, PDO::PARAM_INT);

      $res = $stmt->execute();

      if( $res ) {
        $data = $stmt->fetch();
        //var_dump($data);
      }
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
    return $data;
  }

  public function createDataFunction()
  {
    $dataArray = [];
    $memoContents = $this->getMemoContents();
    // グループ分け
    $data = $this->arrayGroupBy($memoContents, 'main_id');
    foreach ($data as $key => $value) {
      $memoTitles = $this->getMemoTitle();
      $memoTitleId = array_search($key, array_column($memoTitles, 'id'));
      $dataArray[$key] = [
        "main_id" => $key,
        "memo_title" => $memoTitles[$memoTitleId]['title'],
        "contents" => $value,
      ];
    }

    return $dataArray;
  }

  private function arrayGroupBy(array $items, $keyName)
  {
    $groups = [];
    foreach ($items as $item) {
        $key = $item[$keyName];
        if (array_key_exists($key, $groups)) {
            $groups[$key][] = $item;
        } else {
            $groups[$key] = [$item];
        }
    }
    return $groups;
  }

  public function insertMemoContentsAll($mainTitle, $title, $content)
  {
    $sql =<<<SQL
INSERT INTO
  memo_title(title)
VALUES (:title);
SQL;

  try {
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':title', $mainTitle);
    $data = $stmt->execute();
  } catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
  }
  $id = $this->pdo->lastInsertId();

    $sql =<<<SQL
INSERT INTO
  memo_contents(main_id, title, content)
VALUES (?, ?, ?);
SQL;

  $params = [
    intval($id),
    htmlspecialchars($title, ENT_QUOTES, "utf-8"),
    $content,
  ];

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }

  public function updateContent($id, $oyaId, $title, $content)
  {
    $sql =<<<SQL
UPDATE
  memo_contents
SET
  title = :title,
  content = :content
WHERE
  id = :id
AND
  main_id = :main_id
SQL;

  try {
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':content', $content);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':main_id', $oyaId);
    $data = $stmt->execute();
  } catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
  }
  }

  public function insertMemoContents($mainId, $title, $content)
  {
    $sql =<<<SQL
INSERT INTO
  memo_contents(main_id, title, content)
VALUES (?, ?, ?);
SQL;

  $params = [
    intval($mainId),
    htmlspecialchars($title, ENT_QUOTES, "utf-8"),
    $content,
  ];

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }

  public function deleteMemoContents($id, $mainId)
  {
    $sql =<<<SQL
DELETE FROM
  memo_contents
WHERE
  id = ?
AND
  main_id = ?;
SQL;

  $params = [
    intval($id),
    intval($mainId),
  ];

    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }

  public function endPdo()
  {
    $this->pdo = null;
  }

  public function insertTestData()
  {
    $sql =<<<SQL
INSERT INTO
  memo_title(title)
VALUES (?);
SQL;
    try {
      $stmt = $this->pdo->prepare($sql);
      foreach ([['test1'], ['test2'], ['test3'], ['test4']] as $params) {
        $stmt->execute($params);
      }
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }

  public function insertTestData2()
  {
    $sql =<<<SQL
INSERT INTO
  memo_contents(main_id, title, content)
VALUES (?, ?, ?);
SQL;

$test =<<<MB
# TEST

・ sample
・ sample2
MB;
    try {
      $stmt = $this->pdo->prepare($sql);
      foreach ([
        [1,'test1', $test],
        [1,'test2', $test],
        [1,'test3', $test],
        [1,'test4', $test],
        [2,'test1', $test],
        [2,'test2', $test],
        [2,'test3', $test],
        [2,'test4', $test]
      ] as $params) {
        $stmt->execute($params);
      }
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }
}

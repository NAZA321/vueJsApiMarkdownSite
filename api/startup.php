<?php
// 初回読み込みさせる
header("Content-type: text/html; charset=utf-8");
//呼び出された際にDBファイルが存在するか確認
$dbFile = "sqlite.db";

if (file_exists("./" . $dbFile)) {
  // 2回目以降アクセスされた場合は日付とバックアップ作成のみ実行させる
  // バックアップが完了次第日付の上書き
  $fileHandle = fopen( "./data.txt", "rw");
  $txt = fgets($fileHandle);
  if ($today != $txt) {
    $flg = copy($dbFile, './bk/' . $dbFile . '.' .$today);
    fwrite( $fileHandle, $today);
  }
  fclose($fileHandle);
} else {
  // 存在しない場合はDB作成
  touch($dbFile);
  // 併せて今日の日付のtextファイルを作成しておく
  $fileHandle = fopen( "./data.txt", "w");
  $today = date("Ymd");
  fwrite( $fileHandle, $today);
  fclose($fileHandle);
  $createDb = new CreateDB($dbFile);

  $createDb->connectPdo();
  $createDb->createTable();
  $createDb->endPdo();
}

echo "すべての処理が完了しました。";


class CreateDB
{
  private $dbFile = "";
  private $pdo = null;
  function __construct($fileName)
  {
    $this->dbFile = $fileName;
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

  public function createTable()
  {
    $memoTitleSql =<<<SQL
CREATE TABLE memo_title (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    created_at TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    updated_at TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime'))
);
CREATE TRIGGER trigger_memo_title_updated_at AFTER UPDATE ON memo_title
BEGIN
    UPDATE memo_title SET updated_at = DATETIME('now', 'localtime') WHERE rowid == NEW.rowid;
END;
SQL;

  $memoContentsSql =<<<SQL
CREATE TABLE memo_contents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    main_id INTEGER,
    title TEXT,
    content TEXT,
    created_at TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    updated_at TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime'))
);
CREATE TRIGGER trigger_memo_contents_updated_at AFTER UPDATE ON memo_contents
BEGIN
    UPDATE memo_contents SET updated_at = DATETIME('now', 'localtime') WHERE rowid == NEW.rowid;
END;
SQL;

    // テーブル作成
    try {
      $this->pdo->exec($memoTitleSql);
      $this->pdo->exec($memoContentsSql);
    } catch (Exception $e) {
      echo $e->getMessage() . PHP_EOL;
    }
  }

  public function endPdo()
  {
    $this->pdo = null;
  }
}

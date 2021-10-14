<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>メモ</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vue-js-modal@1.3.31/dist/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/github-markdown-css@3.0.1/github-markdown.min.css">
    <link rel="stylesheet" href="https://unpkg.com/vue-select@latest/dist/vue-select.css">
    <script src="https://unpkg.com/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js"></script>
    <script src="https://unpkg.com/vue-router/dist/vue-router.js"></script>
    <script src="https://unpkg.com/vue-material@beta"></script>
    <script src="https://unpkg.com/vue-markdown@2.2.4/dist/vue-markdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-js-modal@1.3.31/dist/index.min.js"></script>
    <script src="https://unpkg.com/vue-select@latest"></script>
  </head>
  <body>
    <header>
      <h1>メモ一覧</h1>
    </header>

    <main id="app" class="clearfix">
      <aside id="sidebar">
        <p><router-link to="/">TOP</router-link></p>
        <p><router-link to="/create">新規作成</router-link></p>
        <p class="oya_title">メモタイトル一覧</p>
        <ul class="oya_list">
          <li v-for="(memoList, index) in memoLists">
            <p>{{ memoList.memo_title }}</p>
            <ul>
              <li v-for="content in memoList.contents">
                <router-link :to="{name:'content', params: {mainId: memoList.main_id, contentId:content.id}}">{{ content.title }}</router-link>
              </li>
            </ul>
          </li>
        </ul>
      </aside>

      <div id="contents">
        <router-view></router-view>
      </div>
    </main>

    <footer></footer>
    <script type="x-template" id="top_tmplete">
    <?php
    include("./template/top.php");
    ?>
    </script>
    <script type="x-template" id="content_detail_templete">
    <?php
    include("./template/content_detail.php");
    ?>
    </script>
    <script type="x-template" id="create_tmplete">
    <?php
    include("./template/create.php");
    ?>
    </script>
    <script type="x-template" id="update_tmplete">
    <?php
    include("./template/update.php");
    ?>
    </script>
    <script src="./js/script.js"></script>
  </body>
</html>

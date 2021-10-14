# vueJsApiMarkdownSite

Vue.Js を利用した簡易Markdown サイト

api phpで自作している
DBはsqlite3を使用

MAMP設定時の起動方法

https://localhost/api/startup.php

でsqlite.dbを作成

dbは壊れと時のためにbkアップファイルを作成している
1週間前のバックアップは削除される


DB
親タイトル
table memo_title
  id
  title
  created_at
  updated_at

子内容
table memo_contents
  id
  main_id
  title
  content
  created_at
  updated_at

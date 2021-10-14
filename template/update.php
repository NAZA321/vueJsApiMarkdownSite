<div class="us" style="color: #fdfdfdfd;">
  <h2>新規作成</h2>
  <form class="newCreate" action="" method="post">
    <!--
    <div class="">
      <p>親タイトル設定</p>
      <div class="" style="background:#fff;">
        <v-select :options="oya_list" v-model="selected" label='title'></v-select>
      </div>

      <div class="" v-if="selected.id == 0">
        新規親タイトル:<input type="text" v-model="oya_title" placeholder="親タイトルを設定記載">
      </div>
    </div>
    -->
    <div class="">
      <span>タイトル : </span>
      <input type="text" v-model="ko_title" placeholder="子タイトルを設定記載" :source="ko_title">
    </div>

    <div class="editor_chenge">
      <a href="javascript:void(0)" v-on:click="editor()">エディター</a>
      <a href="javascript:void(0)" v-on:click="preview()">プレビュー</a>
      <!--<a href="javascript:void(0)" v-on:click="show" class="modal_his">MarkDown 記述方法</a>-->
    </div>
    <textarea class="editor" v-model="source"  v-show="editor_show"></textarea>
    <div class="content_preview markdown-body" v-show="preview_show">
      <vue-markdown :source="source"></vue-markdown>
    </div>

    <div class="submit_btm">
      <a href="javascript:void(0)" v-on:click="submit()">更新</a>
    </div>
  </form>
  <modal name="hello-world" :draggable="false" :resizable="false">
    <div class="modal-header">
      <h2>Modal title</h2>
    </div>
    <div class="modal-body">
      <p>you're reading this text in a modal!</p>
      <button v-on:click="hide">閉じる</button>
    </div>
  </modal>
</div>

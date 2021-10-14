<div>
	<div v-show="loading" class="loader">Now loading...</div>
	<div v-show="!loading" class="itemContainer">
		<div class="us">
			<div class="update_link clearfix">
				<router-link :to="{name:'update', params: {mainId: $route.params.mainId, content:$route.params.contentId}}">更新</router-link>
				<a href="javascript:void(0)" v-on:click="deleteBtn()">削除</a>
			</div>
			<h2 class="content_title">{{content.title}}</h2>

			<div class="content_preview preview markdown-body">
				<vue-markdown :source="source"></vue-markdown>
			</div>
		</div>
	</div>
</div>

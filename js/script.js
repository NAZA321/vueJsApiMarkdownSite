const apiUrl = 'http://localhost/api/run.php'
Vue.use(VueMaterial.default)
Vue.use(VueMarkdown);
const VModal = window["vue-js-modal"].default
Vue.use(VModal);
const vSelect = VueSelect.VueSelect;
Vue.component('v-select', vSelect);
const Top = {
	template: '#top_tmplete'
}

const Create = {
	template: '#create_tmplete',
	data: function () {
		return {
			source: '# 初期',
			oya_title: '',
			ko_title: '',
			editor_show: true,
			preview_show: false,
			selected: {'id':0, 'title':'新規作成'},
			oya_list: [],
			complete: [],
		}
	},
	created: function () {
    this.fetchData()
  },
	methods: {
		editor: function () {
			this.editor_show = true;
			this.preview_show = false;
		},
		preview: function () {
			this.editor_show = false;
			this.preview_show = true;
		},
		show : function() {
      this.$modal.show('hello-world');
    },
    hide : function () {
      this.$modal.hide('hello-world');
    },
		submit: function () {
			if (this.selected.id == 0) {
				if (this.oya_title == "") {
					alert('未入力の値があります');
					return false;
				}
				if (this.ko_title == "") {
					alert('未入力の値があります');
					return false;
				}
				if (this.source == "") {
					alert('未入力の値があります');
					return false;
				}

				if (confirm('登録して問題ないですか？')) {
						this.createNewContentAll();
						alert('登録完了しました。');
						window.location.href = '/';
				}
			} else {
				if (this.ko_title == "") {
					alert('未入力の値があります');
					return false;
				}
				if (this.source == "") {
					alert('未入力の値があります');
					return false;
				}

				if (confirm('登録して問題ないですか？')) {
						this.createNewContent();
						alert('登録完了しました。');
						window.location.href = '/';
				}
			}
			//console.log(this.selected.id);
			//console.log(this.source);
		},
		fetchData: function () {
			axios
				.post(apiUrl, {
					'type': 1,
				})
				.then(response => {
					this.oya_list = response.data;

					//console.log(this.oya_list);
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
		createNewContentAll: function () {
			axios
				.post(apiUrl, {
					'type': 'contentAll',
					'oya_title': this.oya_title,
					'title': this.ko_title,
					'source': this.source
				})
				.then(response => {
					this.complete = response.data;
					//console.log(this.complete);
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
		createNewContent: function () {
			axios
				.post(apiUrl, {
					'type': 'content',
					'oya_id': this.selected.id,
					'title': this.ko_title,
					'source': this.source
				})
				.then(response => {
					this.complete = response.data;
					//console.log(this.complete);
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
	}
}

const Content = {
	template: '#content_detail_templete',
	data: function() {
		return {
			content: [],
			source: '',
			loading: false
		}
	},
	created: function () {
    this.fetchData()
  },

  watch: {
    '$route': 'fetchData'
  },
	methods: {
		fetchData: function () {
			this.loading = true;
			axios
				.post(apiUrl, {
					'main_id': this.$route.params.mainId,
					'content_id': this.$route.params.contentId
				})
				.then(response => {
					this.content = response.data;
					this.source = this.content.content;
					// 0.5秒遅延させる loadingが見たいだけ
					setTimeout(() => {
						this.loading = false;
					}, 500)
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
		deleteBtn: function() {
			if (confirm('削除しますか？')) {
					this.deleteContent();
					alert('削除完了しました。');
					window.location.href = '/';
			}
		},
		deleteContent: function() {
			axios
				.post(apiUrl, {
					'type': 'delete',
					'main_id': this.$route.params.mainId,
					'content_id': this.$route.params.contentId
				})
				.then(response => {
					this.content = response.data;
					console.log(this.content)
					//this.source = this.content.content;
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		}
	}
}

const Update = {
	template: '#update_tmplete',
	data: function () {
		return {
			oya_title: '',
			ko_title: '',
			content: [],
			source: '',
			editor_show: true,
			preview_show: false,
			selected: {'id':0, 'title':'新規作成'},
			oya_list: [],
			complete: [],
		}
	},
	created: function () {
    this.fetchData()
		this.fetchDetailData()
  },
	methods: {
		editor: function () {
			this.editor_show = true;
			this.preview_show = false;
		},
		preview: function () {
			this.editor_show = false;
			this.preview_show = true;
		},
		show : function() {
      this.$modal.show('hello-world');
    },
    hide : function () {
      this.$modal.hide('hello-world');
    },
		submit: function () {
			if (this.ko_title == "") {
				alert('未入力の値があります');
				return false;
			}
			if (this.source == "") {
				alert('未入力の値があります');
				return false;
			}

			if (confirm('更新して問題ないですか？')) {
					this.updateContent();
					alert('更新完了しました。');
					window.location.href = '/';
			}
		},
		fetchDetailData: function () {
			this.loading = true;
			axios
				.post(apiUrl, {
					'main_id': this.$route.params.mainId,
					'content_id': this.$route.params.contentId
				})
				.then(response => {
					this.content = response.data;
					this.ko_title = this.content.title;
					this.source = this.content.content;
					// 0.5秒遅延させる loadingが見たいだけ
					setTimeout(() => {
						this.loading = false;
					}, 500)
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
		fetchData: function () {
			axios
				.post(apiUrl, {
					'type': 1,
				})
				.then(response => {
					this.oya_list = response.data;
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
		updateContent: function () {
			axios
				.post(apiUrl, {
					'type': 'update',
					'id': this.$route.params.contentId,
					'oya_id': this.$route.params.mainId,
					'title': this.ko_title,
					'source': this.source
				})
				.then(response => {
					this.complete = response.data;
					console.log(this.complete);
				})
				.catch(err => {
					if(err.response) {
						// レスポンスが200以外の時の処理
					}
				})
		},
	}
}



const router = new VueRouter({
  //mode: 'history',
  routes: [
    {
      path: '/',
			name: "top",
      component: Top
    },
    {
      path: '/:mainId/:contentId',
      name: "content",
      component: Content
		},
		{
      path: '/create',
      name: "create",
      component: Create
		},
		{
      path: '/update/:mainId/:contentId',
      name: "update",
      component: Update
		},
  ]
})
//router.push('/')

new Vue({
  el: '#app',
  data: function() {
    return {
      memoLists: [],
			hiddenCards: [],
    }
  },
  mounted: function ()  {
    axios
      .get(apiUrl)
      .then(response => {
				this.memoLists = response.data
				//console.log(this.memoLists)
			})
  },
	router
})

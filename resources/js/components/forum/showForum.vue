<template>
    <div id="app">
        <br><br><br><br>
        <v-app id="inspire">
            <v-progress-linear v-if="isLoadingForum" v-slot:progress :color="colorDefault"
                               indeterminate></v-progress-linear>


            <v-layout v-else align-content-center>
                <v-flex xs12 sm10 offset-sm1>
                    <v-card>
                        <v-card-text>
                            <h2>
                                <strong>{{forum.titulo}}</strong>
                            </h2>
                            <br>
                            <h4>
                                {{forum.descricao}}
                            </h4>
                            <br>
                            <div>
                                <span>patrimonios relacionados:</span>
                                <v-layout>
                                    <h5 v-for="(patrimonio, index) in forum.patrimonios" :key="index"
                                        class="blue--text font-weight-light">
                                        <a @click="$router.push('/patrimonio/'+ patrimonio.id)">
                                            {{patrimonio.nome}}&nbsp &nbsp
                                        </a>
                                    </h5>
                                </v-layout>
                            </div>
                        </v-card-text>
                        <div v-if="isLoadingComentarios">
                            <v-progress-linear v-slot:progress :color="colorDefault"
                                               indeterminate></v-progress-linear>
                        </div>
                        <div v-else>
                            <v-card>
                                <v-card-text>
                                    <h4>Comentários:</h4><br>
                                    <div v-if="comentarios.length == 0">
                                        <v-card-text>
                                            <center>
                                                <h3>
                                                    <span> 0 comentários</span>
                                                </h3>
                                            </center>
                                        </v-card-text>
                                    </div>
                                    <v-data-table v-else :headers="headers" class="elevation-1" :items=comentarios
                                                  hide-headers :pagination.sync="pagination"
                                                  :loading="isLoadingComentarios">
                                        <template v-slot:items="props">
                                            <tr>
                                                <td class="text-md-left">
                                                    <br>
                                                    <center>
                                                        <h3 :class="props.item.likes - props.item.dislikes < 0 ? 'error--text' : 'green--text'">
                                                            {{classificacao(props.item)}}</h3>
                                                    </center>
                                                    <v-layout>
                                                        <v-flex sm6>
                                                            <center>
                                                                <v-btn flat icon @click="like(props.item)">
                                                                    <v-icon color="green">{{likeIcon(props.item.id)}}
                                                                    </v-icon>
                                                                </v-btn>
                                                                <span class="green--text">{{props.item.likes}}</span>
                                                            </center>
                                                        </v-flex>
                                                        <v-spacer></v-spacer>
                                                        <v-flex sm6>
                                                            <center>
                                                                <v-btn flat icon @click="dislike(props.item)">
                                                                    <v-icon color="red" class="far">{{dislikeIcon(props.item.id)}}
                                                                    </v-icon>
                                                                </v-btn>
                                                                <span class="error--text">{{props.item.dislikes}}</span>
                                                            </center>
                                                        </v-flex>
                                                    </v-layout>
                                                </td>
                                                <td class="text-lg-center">
                                                    <html style="overflow: hidden;">
                                                    {{props.item.comentario}}
                                                    </html>
                                                    <span class="grey--text">{{props.item.data_criado}}</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </v-data-table>

                                    <br>
                                    <br>
                                    <v-card-text>
                                        <v-card sm10 offset-sm1>
                                            <v-card-text>
                                                <div>
                                                    <h4>
                                                        Escrever comentário:
                                                    </h4>
                                                </div>
                                                <div v-if="!$store.state.user">
                                                    <v-text-field id="inputEmail"
                                                                  v-model="meuComentario.email"
                                                                  label="O seu email"
                                                                  :rules="[(v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido']"
                                                                  clearable
                                                    >
                                                        <template v-slot:append>
                                                            <v-tooltip bottom>
                                                                <template v-slot:activator="{ on }">
                                                                    <v-icon v-on="on">help</v-icon>
                                                                </template>
                                                                <span>O seu email será utilizado apenas para proteger o seu comentario.</span>
                                                            </v-tooltip>
                                                        </template>
                                                    </v-text-field>
                                                </div>
                                                <br>
                                                <div>
                                                    <ckeditor :editor="editor" :config="editorConfig"
                                                              :value="meuComentario.comentario"
                                                              v-model="meuComentario.comentario"></ckeditor>
                                                </div>
                                                <div v-if="meuComentario.comentario">
                                                    <v-spacer></v-spacer>
                                                    <v-btn @click="saveComentario" color="success">Publicar</v-btn>
                                                </div>
                                            </v-card-text>
                                        </v-card>
                                    </v-card-text>
                                </v-card-text>
                            </v-card>
                        </div>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-app>
        <br>
    </div>

</template>
<script type="text/javascript">
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        props: ['id'],
        data: function () {
            return {
                forum: {},
                comentarios: [],
                isLoadingForum: {},
                isLoadingComentarios: {},
                meuComentario: {},
                meuLikes: [],
                meuDislikes: [],

                editor: ClassicEditor,
                editorConfig: {
                    toolbar: ['heading', '|', 'Bold', 'Italic', 'bulletedList', 'numberedList', 'blockQuote', 'Undo', 'Redo'],
                    heading: {
                        options: [
                            {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                            {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                            {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                            {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                        ]
                    },

                },
                headers: [
                    {
                        text: 'classificacao',
                        align: 'left',
                        sortable: false,
                        value: 'classificacao'
                    },
                    {text: 'comentario', value: 'comentario', sortable: false, align: 'center'},
                    {text: '', value: 'actions', sortable: false, align: 'center'},
                ],
                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 5,
                    sortBy: 'classificacao',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
            };
        },
        methods: {
            getForum() {
                this.isLoadingForum = true;
                axios.get('/api/forums/' + this.id)
                    .then(response => {
                        this.isLoadingForum = false;
                        this.forum = response.data.data;
                        this.setCookies();
                    })
                    .catch(error => {
                        this.isLoadingForum = false;
                        this.toastErrorApi(error);
                    });
            },
            getComentarios() {
                this.isLoadingComentarios = true;
                axios.get('/api/forums/' + this.id + '/comentarios')
                    .then(response => {
                        this.isLoadingComentarios = false;
                        //if (response.data.data) {
                        this.comentarios = response.data.data;
                        // }
                    })
                    .catch(error => {
                        this.isLoadingComentarios = false;
                        this.toastErrorApi(error);
                    });
            },
            classificacao(comentario) {
                let classif = comentario.likes - comentario.dislikes;
                if (classif < 0) {
                    return classif;
                }
                return "+" + classif;
            },
            saveComentario(url = 'api/forums/' + this.forum.id + '/comentarios') {
                axios.post(url)
                    .then(response => {
                        this.getComentarios();
                        this.toastPopUp('success', 'Comentario criado!');
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                    });
            },
            like(comentario) {
                if (this.meuDislikes.includes(comentario.id)){
                    this.dislike(comentario);
                }
                let tipo = "";
                if(this.meuLikes.includes(comentario.id)){ //decrementar
                    this.meuLikes = this.removeItemFromArray(this.meuLikes, comentario.id);
                    comentario.likes--;
                    tipo = "decrementLike";
                }else{
                    this.meuLikes.push(comentario.id);
                    comentario.likes++;
                    tipo = "incrementLike";
                }

                axios.put('/api/comentarios/' + this.forum.id +"/" + tipo).then(response => {
                    if (tipo == "incrementLike"){
                        this.updateCookies(1,1, comentario.id);
                    } else {
                        this.updateCookies(1,0, comentario.id);
                    }
                })
                    .catch(error => {
                        this.toastErrorApi(error);
                        if (tipo == "incrementLike"){
                            this.meuLikes = this.removeItemFromArray(this.meuLikes, comentario.id);

                            comentario.likes--;
                        } else {
                            comentario.likes++;
                            this.meuLikes.push(comentario.id);
                        }
                    });
            },
            dislike(comentario) {
                if (this.meuLikes.includes(comentario.id)){
                    this.like(comentario);
                }
                let tipo = "";
                if(this.meuDislikes.includes(comentario.id)){ //decrementar
                    comentario.dislikes--;
                    this.meuDislikes = this.removeItemFromArray(this.meuDislikes, comentario.id);
                    tipo = "decrementDislike";
                }else{
                    comentario.dislikes++;
                    this.meuDislikes.push(comentario.id);
                    tipo = "incrementDislike";
                }

                axios.put('/api/comentarios/' + this.forum.id +"/"+ tipo).then(response => {
                    if (tipo == "incrementDislike"){
                        this.updateCookies(0,1, comentario.id);
                    } else {
                        this.updateCookies(0,0, comentario.id);
                    }
                })
                    .catch(error => {
                        this.toastErrorApi(error);
                        if (tipo == "incrementDislike"){
                            comentario.dislikes--;
                            this.meuDislikes = this.removeItemFromArray(this.meuDislikes, comentario.id);

                        } else {
                            comentario.dislikes++;
                            this.meuDislikes.push(comentario.id);
                        }
                    });
            },
            setCookies() {
                //Default Likes cookie name = "forumTitulo"+"ForumId"+ "-" + "likes" EX: ComoVisitarContento19-likes
                //Default Dislikes cookie name = "forumTitulo"+"ForumId"+ "-" + "dislikes" EX: ComoVisitarContento19-dislikes

                let cookieName = this.forum.titulo.replace(/\s/g, "").concat(this.forum.id);

                if (this.$cookies.isKey(cookieName + "-likes")) {
                    this.meuLikes = this.$cookies.get(cookieName + "-likes").split(',').map(function(item) {
                        return parseInt(item, 10);
                    });
                } else {
                    this.$cookies.set(cookieName + "-likes", [] , "1y");
                }
                if (this.$cookies.isKey(cookieName + "-dislikes")) {
                    this.meuDislikes = this.$cookies.get(cookieName + "-dislikes").split(',').map(function(item) {
                        return parseInt(item, 10);
                    });
                } else {
                    this.$cookies.set(cookieName + "-dislikes", [], "1y");
                }
                console.log(this.$cookies.get(cookieName + "-likes"));
                console.log(this.meuLikes);
            },
            updateCookies(like, incrementar, comentarioId){
                let cookieName = this.forum.titulo.replace(/\s/g, "").concat(this.forum.id);

                if(like){
                    if (incrementar){
                        if(!this.meuLikes.includes(comentarioId)) {
                            this.meuLikes.push(comentarioId);
                        }
                    }else{
                        if (this.meuLikes.includes(comentarioId)){
                            this.meuLikes = this.removeItemFromArray(this.meuLikes, comentarioId);

                        }
                    }
                    this.$cookies.remove(cookieName + "-likes");
                    this.$cookies.set(cookieName + "-likes", this.meuLikes, "1y");
                }else{
                    if (incrementar){
                        if(!this.meuDislikes.includes(comentarioId)) {
                            this.meuDislikes.push(comentarioId);
                        }
                    }else{
                        if (this.meuDislikes.includes(comentarioId)){
                            this.meuDislikes = this.removeItemFromArray(this.meuDislikes, comentarioId);
                        }
                    }
                    this.$cookies.remove(cookieName + "-dislikes");
                    this.$cookies.set(cookieName + "-dislikes", this.meuDislikes, "1y");
                }
            },
            removeItemFromArray(array, item){
                for (let i = 0; i < array.length; i++) {
                    if (array[i] == item) {
                        array.splice(i, 1);
                        i--;
                    }
                }
                return array;
            },

            likeIcon(comentarioId){
                if(this.meuLikes.includes(comentarioId)){
                    return "fas fa-thumbs-up";
                }
                return "far fa-thumbs-up";
            },
            dislikeIcon(comentarioId){
                if(this.meuDislikes.includes(comentarioId)){
                    return "fas fa-thumbs-down";
                }
                return "far fa-thumbs-down";
            }
        },

        mounted() {
            this.getForum();
            this.getComentarios();
        }
    }
</script>

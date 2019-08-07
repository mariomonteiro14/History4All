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
                            <div style="font-size: 25px" v-html="forum.descricao"></div>
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
                                                                    <v-icon color="red" class="far">
                                                                        {{dislikeIcon(props.item.id)}}
                                                                    </v-icon>
                                                                </v-btn>
                                                                <span class="error--text">{{props.item.dislikes}}</span>
                                                            </center>
                                                        </v-flex>
                                                    </v-layout>
                                                </td>
                                                <td class="text-lg-center">
                                                    <div style="overflow: hidden;font-size: 15px;"
                                                         v-html="props.item.comentario"></div>
                                                    <v-layout style="padding-right: 20px">
                                                        <v-spacer></v-spacer>
                                                        <span class="grey--text">{{props.item.data}}</span>
                                                    </v-layout>
                                                </td>
                                                <v-speed-dial
                                                    v-model="fab"
                                                    direction="left"
                                                >
                                                    <template v-slot:activator>
                                                        <v-btn
                                                            v-model="fab"
                                                            color="blue darken-2"
                                                            dark
                                                            flat
                                                            small
                                                            fab
                                                            @click="changeFabId(props.item.id)"
                                                        >
                                                            <v-icon v-if="fab">close</v-icon>
                                                            <v-icon color="grey" v-else>fas fa-ellipsis-v</v-icon>
                                                        </v-btn>
                                                    </template>
                                                    <v-list class="pa-0" absolute dense>
                                                        <v-list-tile ripple="ripple" rel="noopener">
                                                                <v-btn
                                                                    flat
                                                                    small
                                                                    color="green"
                                                                >
                                                                    editar
                                                                </v-btn>
                                                                <v-btn
                                                                    flat
                                                                    small
                                                                    color="red"
                                                                >
                                                                    Eliminar
                                                                </v-btn>
                                                                <v-btn
                                                                    flat
                                                                    small
                                                                    color="indigo"
                                                                >
                                                                    Denunciar
                                                                </v-btn>
                                                        </v-list-tile>
                                                    </v-list>
                                                </v-speed-dial>
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
                                                                  v-model="meuComentario.userEmail"
                                                                  label="O seu email"
                                                                  :rules="[(v) =>  /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido']"
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
                                                    <v-btn v-if="!sendingComentario" @click="saveComentario"
                                                           color="success" :disabled="hasErrors">Publicar
                                                    </v-btn>
                                                    <loader style="padding: 10px" v-else color="green"
                                                            size="32px"></loader>
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
        <v-dialog
            v-model="dialogCode"
            width="450px"
        >
            <v-card>
                <v-card-title class="headline">Verificação de email</v-card-title>

                <v-card-text>
                    Foi enviado um email para o endereço inserido. Introduza o codigo presente nesse email para concluir o processo.
                    <v-text-field class="text-sm-left"
                                  v-model="credenciais.codigo"
                                  label="Código de acesso"
                                  :rules="[v => !!v || 'Código de acesso']"
                                  clearable
                                  :disabled="sendingComentario"
                    />
                </v-card-text>

                <v-card-actions v-if="!verificandoCredenciais">
                    <v-spacer></v-spacer>

                    <v-btn
                        color="green darken-1"
                        text
                        flat
                        @click="gerarCodigo"
                    >
                        Reenviar email
                    </v-btn>

                    <v-btn
                        color="orange darken-1"
                        text flat
                        @click="verificarCodigo(1)"
                        :disabled="!credenciais.codigo"
                    >
                        Enviar
                    </v-btn>
                </v-card-actions>
                <v-card-actions v-else>
                    <v-spacer></v-spacer>
                    <loader style="padding: 10px" color="green"
                            size="32px"></loader>
                </v-card-actions>
            </v-card>
        </v-dialog>
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
                sendingComentario: false,
                verificandoCredenciais: false,
                credenciais: {},
                fab: false,
                //fabId: 0,
                dialogCode: false,

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
        mounted() {
            this.getForum();
            this.getComentarios();
            if (this.$cookies.isKey("credentials")){
                this.credenciais = this.$cookies.get("credentials")
            }
            console.log(this.$cookies.get("credentials"));
        },
        methods: {
            getForum() {
                this.isLoadingForum = true;
                axios.get('/api/forums/' + this.id)
                    .then(response => {
                        this.isLoadingForum = false;
                        this.forum = response.data.data;
                        if (this.forum.numComentarios > 0) {
                            this.setCookies();
                        }
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
                        this.comentarios = response.data.data;
                    })
                    .catch(error => {
                        this.isLoadingComentarios = false;
                        this.toastErrorApi(error);
                    });
            },
            classificacao(comentario) {
                let classif = comentario.likes - comentario.dislikes;
                if (classif > 0) {
                    return "+" + classif;
                }
                return classif;
            },
            changeFabId(id){
                if(this.fabId != id){
                    this.fabId= id;
                }else{
                    this.fabId = 0;
                }
            },

            saveComentario() {
                if (this.meuComentario.userEmail && (!this.credenciais.codigo || this.credenciais.email != this.meuComentario.userEmail)){
                    this.dialogCode = true;
                    this.credenciais.codigo= null
                    this.gerarCodigo();
                    return;
                }

                this.sendingComentario = true;
                if (this.$store.state.user) {
                    this.meuComentario.userEmail = this.$store.state.user.email;
                }else{
                    this.meuComentario.codigo = this.credenciais.codigo;
                }
                axios.post('/api/forums/' + this.forum.id + '/comentarios', this.meuComentario)
                    .then(response => {
                        this.getComentarios();
                        this.sendingComentario = false;
                        this.meuComentario = {};
                        this.toastPopUp('success', 'Comentario criado!');
                    })
                    .catch(error => {
                        this.sendingComentario = false;
                        console.log(error);
                        if (error.response.data.message == "Codigo introduzido invalido!"){
                            this.credenciais.codigo = null;
                            this.saveComentario();
                        } else {
                            this.toastErrorApi(error);
                        }
                    });
            },
            gerarCodigo(){
                this.isLoading= true;
                axios.post('/api/forums/generateAccessCode', {'user_email': this.meuComentario.userEmail}).then(response => {
                }).catch(error => {
                    //this.dialogCode = false;
                    this.toastErrorApi(error);
                })
            },
            verificarCodigo(funcao = 0){
                this.verificandoCredenciais = true;
                axios.post('/api/forums/compararCodigo', {'user_email': this.meuComentario.userEmail, 'codigo': this.credenciais.codigo}).then(response => {
                    this.verificandoCredenciais = false;
                    if (funcao == 1) { //registar comentario
                        this.dialogCode = false;
                        this.credenciais.email = this.meuComentario.userEmail;
                        this.$cookies.set("credentials", this.credenciais, "1d");
                        this.saveComentario();
                    }
                    return true;
                }).catch(error => {
                    this.verificandoCredenciais = false;
                    this.credenciais.codigo = null;
                    return false;
                })
            },
            like(comentario) {
                if (this.meuDislikes.includes(comentario.id)) {
                    this.dislike(comentario);
                }
                let tipo = "";
                if (this.meuLikes.includes(comentario.id)) { //decrementar
                    this.meuLikes = this.removeItemFromArray(this.meuLikes, comentario.id);
                    comentario.likes--;
                    tipo = "decrementLike";
                } else {
                    this.meuLikes.push(comentario.id);
                    comentario.likes++;
                    tipo = "incrementLike";
                }

                axios.put('/api/comentarios/' + comentario.id + "/" + tipo).then(response => {
                    if (tipo == "incrementLike") {
                        this.updateCookies(1, 1, comentario.id);
                    } else {
                        this.updateCookies(1, 0, comentario.id);
                    }
                })
                    .catch(error => {
                        this.toastErrorApi(error);
                        if (tipo == "incrementLike") {
                            this.meuLikes = this.removeItemFromArray(this.meuLikes, comentario.id);

                            comentario.likes--;
                        } else {
                            comentario.likes++;
                            this.meuLikes.push(comentario.id);
                        }
                    });
            },
            dislike(comentario) {
                if (this.meuLikes.includes(comentario.id)) {
                    this.like(comentario);
                }
                let tipo = "";
                if (this.meuDislikes.includes(comentario.id)) { //decrementar
                    comentario.dislikes--;
                    this.meuDislikes = this.removeItemFromArray(this.meuDislikes, comentario.id);
                    tipo = "decrementDislike";
                } else {
                    comentario.dislikes++;
                    this.meuDislikes.push(comentario.id);
                    tipo = "incrementDislike";
                }

                axios.put('/api/comentarios/' + comentario.id + "/" + tipo).then(response => {
                    if (tipo == "incrementDislike") {
                        this.updateCookies(0, 1, comentario.id);
                    } else {
                        this.updateCookies(0, 0, comentario.id);
                    }
                })
                    .catch(error => {
                        this.toastErrorApi(error);
                        if (tipo == "incrementDislike") {
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

                let cookieName = this.forum.titulo.replace(/\s/g, "").concat("%" + this.forum.id);


                if (this.$cookies.isKey(cookieName + "-likes")) {
                    this.meuLikes = this.$cookies.get(cookieName + "-likes").split(',').map(function (item) {
                        return parseInt(item, 10);
                    });
                }
                if (this.$cookies.isKey(cookieName + "-dislikes")) {
                    this.meuDislikes = this.$cookies.get(cookieName + "-dislikes").split(',').map(function (item) {
                        return parseInt(item, 10);
                    });
                }

            },
            updateCookies(like, incrementar, comentarioId) {
                let cookieName = this.forum.titulo.replace(/\s/g, "").concat("%" + this.forum.id);

                if (like) {
                    if (incrementar) {
                        if (!this.meuLikes.includes(comentarioId)) {
                            this.meuLikes.push(comentarioId);
                        }
                    } else {
                        if (this.meuLikes.includes(comentarioId)) {
                            this.meuLikes = this.removeItemFromArray(this.meuLikes, comentarioId);
                        }
                    }
                    if (this.meuLikes.length == 0 && this.$cookies.isKey(cookieName + "-likes")) {
                        this.$cookies.remove(cookieName + "-likes");
                    } else if (this.meuLikes.length > 0) {
                        this.$cookies.set(cookieName + "-likes", this.meuLikes, "1y");
                    }
                } else {
                    if (incrementar) {
                        if (!this.meuDislikes.includes(comentarioId)) {
                            this.meuDislikes.push(comentarioId);
                        }
                    } else {
                        if (this.meuDislikes.includes(comentarioId)) {
                            this.meuDislikes = this.removeItemFromArray(this.meuDislikes, comentarioId);
                        }
                    }
                    if (this.meuDislikes.length == 0 && this.$cookies.isKey(cookieName + "-dislikes")) {
                        this.$cookies.remove(cookieName + "-dislikes");
                    } else if (this.meuDislikes.length > 0) {
                        this.$cookies.set(cookieName + "-dislikes", this.meuDislikes, "1y");
                    }
                }
            },
            removeItemFromArray(array, item) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i] == item) {
                        array.splice(i, 1);
                        i--;
                    }
                }
                return array;
            },

            likeIcon(comentarioId) {
                if (this.meuLikes.includes(comentarioId)) {
                    return "fas fa-thumbs-up";
                }
                return "far fa-thumbs-up";
            },
            dislikeIcon(comentarioId) {
                if (this.meuDislikes.includes(comentarioId)) {
                    return "fas fa-thumbs-down";
                }
                return "far fa-thumbs-down";
            }
        },
        computed: {
            hasErrors: function () {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.meuComentario.userEmail).toLowerCase()) ||
                    !this.meuComentario.comentario || this.meuComentario.comentario.length <= 5) {
                    return true;
                }
                return  false;
            }
            }
    }
</script>

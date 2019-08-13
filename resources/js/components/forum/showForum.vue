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
                                            <tr :class="(operacao == 2 && comentEdit == props.item.id && showEditForm) ? 'green lighten-4' : ''">
                                                <td class="text-md-left"
                                                    v-if="!(operacao == 2 && comentEdit == props.item.id)">
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
                                                <td class="text-xs-left" v-else></td>
                                                <td v-if="operacao == 2 && comentEdit == props.item.id && showEditForm">
                                                    <div style="padding-bottom: 10px; padding-top: 10px">
                                                        <ckeditor :editor="editor" :config="editorConfig"
                                                                  :value="meuComentario.comentario"
                                                                  v-model="meuComentario.comentario"></ckeditor>
                                                    </div>

                                                </td>
                                                <td v-else class="text-lg-center"
                                                    style="padding-bottom: 10px; padding-top: 10px">
                                                    <div style="overflow: hidden;font-size: 15px;"
                                                         v-html="props.item.comentario"></div>
                                                    <v-layout style="padding-right: 20px">
                                                        <v-spacer></v-spacer>
                                                        <span class="grey--text">{{props.item.data}}</span>
                                                    </v-layout>
                                                </td>
                                                <loader v-if="sendingRequest && props.item.id == comentEdit"
                                                        style="padding: 10px" color="green"
                                                        size="32px"></loader>
                                                <v-layout column
                                                          v-else-if="operacao == 2 && comentEdit == props.item.id && showEditForm">
                                                    <v-btn
                                                        icon
                                                        text
                                                        color="error"
                                                        @click="cancelEdit"
                                                    >
                                                        <v-icon>close</v-icon>
                                                    </v-btn>
                                                    <v-btn
                                                        icon
                                                        text
                                                        color="success"
                                                        @click="saveEdit"
                                                    >
                                                        <v-icon>check</v-icon>
                                                    </v-btn>
                                                </v-layout>
                                                <v-speed-dial v-else
                                                              direction="left"
                                                              v-model="fab"
                                                >
                                                    <template v-slot:activator>
                                                        <v-btn
                                                            color="blue darken-2"
                                                            dark
                                                            :flat="!(fabId == props.item.id && fab)"
                                                            small
                                                            fab
                                                            @click="changeFabId(props.item.id)"
                                                            :color="(fabId == props.item.id && fab)?'blue-grey lighten-5':''"
                                                        >
                                                            <v-icon color=grey v-if="fabId == props.item.id && fab">
                                                                close
                                                            </v-icon>
                                                            <v-icon color="grey" v-else>fas fa-ellipsis-v</v-icon>
                                                        </v-btn>
                                                    </template>
                                                    <v-card v-show="fabId==props.item.id" color="green lighten-4">
                                                        <v-layout>
                                                            <v-btn
                                                                v-if="props.item.hasEmail || ($store.state.user && $store.state.user.tipo=='admin')"
                                                                small
                                                                text
                                                                color="warning"
                                                                @click="showEdit(props.item)"
                                                            >
                                                                Editar
                                                            </v-btn>
                                                            <v-btn
                                                                small
                                                                class="white--text"
                                                                color="red"
                                                                @click="eliminarComentario(props.item.id)"
                                                            >
                                                                Eliminar
                                                            </v-btn>
                                                            <v-btn
                                                                small
                                                                class="white--text"
                                                                color="indigo"
                                                                @click="showDenuncia(props.item.id)"
                                                            >
                                                                Denunciar
                                                            </v-btn>
                                                        </v-layout>
                                                    </v-card>
                                                </v-speed-dial>
                                            </tr>
                                        </template>
                                    </v-data-table>

                                    <br>
                                    <br>
                                    <!--ESCREVER COMENTARIO-->
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
                                                <div v-if="meuComentario.comentario && operacao<2">
                                                    <v-spacer></v-spacer>
                                                    <v-btn v-if="!sendingRequest" @click="saveComentario"
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
        <v-layout align-content-center>
            <v-flex xs1 sm7 offset-sm1>
                <v-btn class="primary--text subheading" round flat @click="$router.go(-1)">
                    <v-icon>fa fa-arrow-left</v-icon>
                    &nbsp Voltar
                </v-btn>
            </v-flex>
        </v-layout>
        <br>
        <v-dialog
            v-model="dialogCode"
            width="450px"
            persistent
        >
            <v-card>
                <v-layout>
                    <v-card-title class="headline">
                        <span>Verificação de email</span>
                    </v-card-title>
                    <v-spacer></v-spacer>
                    <v-btn icon flat @click="dialogCode=false; operacao=0">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-layout>

                <v-card-text v-if="introduzirEmail">
                    <span>Apenas o criador deste comentario poderá edita-lo ou elimina-lo.Introduza o seu email de modo a poder conprovar a sua identidade</span>
                    <v-layout>
                        <v-text-field class="text-sm-left"
                                      v-model="credenciais.email"
                                      label="Email"
                                      :rules="[v => !!v || 'Email obrigatório',
                                        (v) =>  /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido']"
                                      clearable
                                      :disabled="sendingEmail"
                        />
                        <v-btn v-if="!sendingEmail" color="warning" round flat @click="gerarCodigo(credenciais.email)">
                            Enviar
                        </v-btn>
                        <loader style="padding: 10px" v-else color="green"
                                size="32px"></loader>
                    </v-layout>
                </v-card-text>
                <v-card-text v-else>
                    Foi enviado um email para o endereço inserido. Introduza o codigo presente nesse email para concluir
                    o processo.
                    <v-text-field class="text-sm-left"
                                  v-model="credenciais.codigo"
                                  label="Código de acesso"
                                  :rules="[v => !!v || 'Código obrigatório']"
                                  clearable
                                  :disabled="sendingRequest"
                    />
                </v-card-text>

                <v-card-actions v-if="!sendingRequest && !introduzirEmail">
                    <v-spacer></v-spacer>

                    <v-btn
                        color="green darken-1"
                        text
                        flat round
                        @click="gerarCodigo(credenciais.email)"
                    >
                        Reenviar email
                    </v-btn>

                    <v-btn
                        color="orange darken-1"
                        text flat round
                        @click="verificarCodigo"
                        :disabled="!credenciais.codigo"
                    >
                        Enviar
                    </v-btn>
                </v-card-actions>
                <v-card-actions v-else-if="!introduzirEmail">
                    <v-spacer></v-spacer>
                    <loader style="padding: 10px" color="green" size="32px"></loader>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <denuncia-dialog ref="denunciarModal" :isForum="false" :id="comentEdit"></denuncia-dialog>
    </div>

</template>
<script type="text/javascript">
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import DenunciaModal from './denunciarModal';

    export default {
        props: ['id'],
        components: {
            'denuncia-dialog': DenunciaModal,
        },

        data: function () {
            return {
                forum: {},
                comentarios: [],
                isLoadingForum: {},
                isLoadingComentarios: {},
                meuComentario: {},
                meuLikes: [],
                meuDislikes: [],
                sendingRequest: false,
                credenciais: {},
                fab: false,
                fabId: 0,
                dialogCode: false,
                comentEdit: 0,
                introduzirEmail: false,
                sendingEmail: false,
                operacao: 0,
                showEditForm: false,

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
            if (this.$cookies.isKey("credentials")) {
                this.credenciais = this.$cookies.get("credentials")
            } else {
                this.credenciais = {email: '', codigo: ''};
            }
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
            changeFabId(id) {
                if (!this.fab && this.fabId == id) {
                    this.fabId = 0;
                }
                if (this.fabId != id) {
                    this.fabId = id;
                }
            },

            saveComentario() {
                this.operacao = 1;
                if (this.meuComentario.userEmail && (!this.credenciais.codigo || this.credenciais.email != this.meuComentario.userEmail)) {
                    this.dialogCode = true;
                    this.credenciais.email = this.meuComentario.userEmail;
                    this.credenciais.codigo = null;
                    this.introduzirEmail = false;
                    this.gerarCodigo();
                    return;
                }

                this.sendingRequest = true;
                if (this.$store.state.user) {
                    this.meuComentario.userEmail = this.$store.state.user.email;
                } else {
                    this.meuComentario.codigo = this.credenciais.codigo;
                }
                axios.post('/api/forums/' + this.forum.id + '/comentarios', this.meuComentario)
                    .then(response => {
                        this.getComentarios();
                        this.sendingRequest = false;
                        this.meuComentario = {};
                        this.toastPopUp('success', 'Comentario criado!');
                        this.operacao = 0;
                    })
                    .catch(error => {
                        this.sendingRequest = false;
                        console.log(error);
                        if (error.response.data.message == "Codigo introduzido invalido!") {
                            this.credenciais.codigo = null;
                            this.saveComentario();
                        } else {
                            this.toastErrorApi(error);
                        }
                    });
            },
            eliminarComentario(comentarioId = 0) {
                this.operacao = 3;

                if (comentarioId != 0) {
                    this.comentEdit = comentarioId;
                }
                let url;

                if (this.$store.state.user) {
                    url = '/api/comentarios/' + this.comentEdit;
                } else {
                    if (this.credenciais.email && this.credenciais.codigo) {
                        url = '/api/comentarios/' + this.comentEdit + '?codigo=' + this.credenciais.codigo + '&email=' + this.credenciais.email;
                    } else {
                        this.introduzirEmail = true;
                        this.dialogCode = true;
                        return;
                    }
                }
                this.sendingRequest = true;
                axios.delete(url).then(response => {
                    this.operacao = 0;
                    this.dialogCode = false;
                    setTimeout(function () {
                        this.sendingRequest = false;
                    }.bind(this), 500);
                    this.toastPopUp("success", "Comentario Eliminado!");
                    this.getComentarios();
                }).catch(error => {
                    this.sendingRequest = false;
                    if (!this.$store.state.user) {
                        if (error.response.data.message == "Código introduzido invalido!") {
                            this.credenciais.codigo = null;
                            this.eliminarComentario(comentarioId);
                        } else if (error.response.data.message == "Operação negada! O utilizador não é o criador deste comentário") {
                            this.credenciais.email = "";
                            this.eliminarComentario(comentarioId);
                        } else {
                            this.toastErrorApi(error);
                        }
                    } else {
                        this.toastErrorApi(error);
                    }
                });
            },
            saveEdit() {
                let request;

                if (!this.$store.state.user) {
                    request = {
                        'comentario': this.meuComentario.comentario,
                        'email': this.credenciais.email,
                        'codigo': this.credenciais.codigo
                    }
                } else {
                    request = {'comentario': this.meuComentario.comentario}
                }
                this.sendingRequest = true;
                console.log(request);
                axios.put('/api/comentarios/' + this.comentEdit, request).then(response => {
                    this.getComentarios();
                    this.cancelEdit();
                    this.sendingRequest = false;
                }).catch(error => {
                    this.sendingRequest = false;
                    if (error.response.data.message == "Código introduzido invalido!") {
                        this.credenciais.codigo = null;
                        this.saveEdit(comentarioId);
                    } else if (error.response.data.message == "Operação negada! O utilizador não é o criador deste comentário") {
                        this.credenciais.email = "";
                        this.eliminarComentario(comentarioId);
                    } else {
                        this.toastErrorApi(error);
                    }
                });

            },

            gerarCodigo(email = this.credenciais.email) {
                this.isLoading = true;
                this.sendingEmail = true;
                let url, request;
                if (this.operacao == 1) {
                    url = '/api/forums/generateAccessCode';
                    request = {'user_email': email};
                } else {
                    url = '/api/comentarios/' + this.comentEdit + '/compararEmails';
                    request = {'user_email': email, 'generateCode': true};
                    if (this.operacao == 2) {
                        if (this.credenciais.codigo) {
                            request = {'user_email': email, 'generateCode': false}
                        }
                    }
                }

                axios.post(url, request).then(response => {
                    this.introduzirEmail = false;
                    this.sendingEmail = false;
                    this.credenciais.codigo = "";
                }).catch(error => {
                    this.sendingEmail = false;
                    this.toastErrorApi(error);
                })
            }
            ,
            verificarCodigo(aux = 0) {
                this.sendingRequest = true;

                axios.post('/api/forums/compararCodigo', {
                    'user_email': this.credenciais.email,
                    'codigo': this.credenciais.codigo
                }).then(response => {
                    this.$cookies.set("credentials", this.credenciais, "1d");
                    if (this.operacao == 1) { //registar comentario
                        this.dialogCode = false;
                        this.credenciais.email = this.meuComentario.userEmail;
                        this.saveComentario();
                    }
                    else if (this.operacao == 2) {//editar comentario
                        this.sendingRequest = false;
                        this.dialogCode = false;
                        this.showEditForm = true;

                    }
                    else if (this.operacao == 3) {//eliminar comentario
                        this.eliminarComentario();
                    } else {
                        this.sendingRequest = false;
                    }
                    return true;
                }).catch(error => {
                    this.sendingRequest = false;
                    this.credenciais.codigo = null;
                    if (this.operacao == 2 && aux == 1) {
                        this.credenciais.codigo = "";
                        this.dialogCode = true;
                        this.introduzirEmail = true;
                    }
                    return false;
                })
            }
            ,
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
            }
            ,
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
            },

            showDenuncia(comentarioId) {
                this.comentEdit = comentarioId;
                this.$refs.denunciarModal.show = true;
            },
            showEdit(comentario) {
                this.cancelEdit();
                this.meuComentario = comentario;
                this.comentEdit = comentario.id;
                this.operacao = 2;

                if (this.$store.state.user && this.$store.state.user.tipo == "admin") {
                    this.showEditForm = true;
                } else {
                    let email;

                    if (this.$store.state.user) {
                        email = this.$store.state.user.email
                    } else if (this.credenciais.email) {
                        email = this.credenciais.email;
                    } else {
                        this.introduzirEmail = true;
                        this.dialogCode = true;
                        return;
                    }
                    this.sendingRequest = true;
                    axios.post('/api/comentarios/' + comentario.id + '/compararEmails', {
                        'user_email': email,
                        'generateCode': false
                    }).then(response => {
                        if (!this.$store.state.user) {
                            if (this.credenciais.codigo) {
                                this.verificarCodigo(1);
                            } else {
                                console.log("nao tem codigo");
                                this.sendingRequest = false;
                                this.dialogCode = true;
                                this.introduzirEmail = true;
                            }
                        } else {
                            this.sendingRequest = false;
                            this.showEditForm = true;
                        }
                    }).catch(error => {
                        if (!this.$store.state.user) {
                            this.credenciais.email = "";
                            this.credenciais.codigo = null;
                            this.introduzirEmail = true;
                            this.dialogCode = true;
                            this.sendingRequest = false;

                        } else {
                            this.sendingRequest = false;
                            this.toastErrorApi(error);
                        }
                    });
                }

            },
            cancelEdit() {
                this.comentEdit = 0;
                this.meuComentario = {};
                this.operacao = 0;
                this.fabId = 0;
                this.showEditForm = false;
                this.sendingRequest = false;
            }


        }
        ,
        computed: {
            hasErrors: function () {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if ((this.meuComentario.userEmail && !re.test(String(this.meuComentario.userEmail).toLowerCase())) ||
                    !this.meuComentario.comentario || this.meuComentario.comentario.length <= 5) {
                    return true;
                }
                return false;
            }
        }
        ,
        watch: {
            fabId() {
                this.fab = this.fabId == 0;
                // console.log(this.fab + "-"+ this.fabId);
            },
            dialogCode() {

                if (!this.dialogCode) {
                    if (this.$cookies.isKey("credentials")) {
                        this.credenciais = this.$cookies.get("credentials");
                    }
                    this.comentEdit = 0;
                    this.meuComentario = {};
                }
            }

        }

    }
</script>

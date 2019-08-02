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
                                                                <v-btn flat icon @click="like(props.item.id)">
                                                                    <v-icon color="green" class="fas">far fa-thumbs-up
                                                                    </v-icon>
                                                                </v-btn>
                                                                <span class="green--text">{{props.item.likes}}</span>
                                                            </center>
                                                        </v-flex>
                                                        <v-spacer></v-spacer>
                                                        <v-flex sm6>
                                                            <center>
                                                                <v-btn flat icon @click="dislike(props.item.id)">
                                                                    <v-icon color="red" class="far">far fa-thumbs-down
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
                                                    <v-btn color="success">Publicar</v-btn>
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
            like(comentarioId) {
                //se o id tiver no cookie decrementar like

                //senao incrementar like
            },
            dislike(comentarioId) {
                //se o id tiver no cookie decrementar like

                //senao incrementar like
            },
        },
        mounted() {
            this.getForum();
            this.getComentarios();
        }
    }
</script>

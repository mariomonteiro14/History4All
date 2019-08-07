<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <forum-add-edit :forum="forumAtual" :patrimonios="patrimonios" v-on:getFor="getForums()"  v-on:clean="cleanForumAtual()"></forum-add-edit>
            <confirmacao-email :forum="forumAtual" v-on:emailConfirmado="emailConfirmado()" v-on:clean="cleanForumAtual()" v-on:credenciais="receberCredenciais"></confirmacao-email>
            <h3>Fórum</h3>
            <br>
            <v-card append float py-0>
                <v-card-title py-0>
                    <v-container fluid grid-list-md>
                        <v-layout row wrap>
                            <v-flex xs12 sm4>
                                <v-text-field
                                    v-model="search"
                                    append-icon="pesquisa"
                                    label="Pesquisar"
                                    single-line
                                    hide-details
                                    clearable
                                    align-bottom
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm4 d-flex>
                                <v-select
                                    v-model="patrimoniosSelected"
                                    :items="patrimonios"
                                    item-text="nome"
                                    chips
                                    label="Filtrar por patrimónios"
                                    multiple
                                    class="input-group--focused custom"
                                    pa-0="" ma-0=""
                                >
                                    <template v-slot:selection="{ item, index }">
                                        <v-chip v-if="index === 0">
                                            <span>{{ item.nome }}</span>
                                        </v-chip>
                                        <span
                                            v-if="index === 1"
                                            class="grey--text caption"
                                        >(+{{ patrimoniosSelected.length - 1 }} outros)</span>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-btn round color="success" data-toggle="modal"
                                data-target="#addForumModal">
                                <v-icon>add</v-icon> &nbsp Fórum
                            </v-btn>
                        </v-layout>
                    </v-container>
                </v-card-title>
            </v-card>
            <v-data-table :headers="headers" :items="filteredForums" :search="search" class="elevation-1"
                          :pagination.sync="pagination" :loading="isLoading">
                <template v-slot:items="props">
                    <tr>
                        <td class="text-xs-left" @click="showForum(props.item)"><b>{{ props.item.titulo }}</b></td>
                        <td class="text-xs-left" @click="showForum(props.item)">{{ props.item.numComentarios }}</td>
                        <td class="text-xs-left" @click="showForum(props.item)">{{ new Date(props.item.data_ultima_atualizacao_comentario).toLocaleString() }}</td>
                        <td class="justify-left layout px-0">
                            <v-btn flat icon @click="editarForumConfirmacao(props.item, 'editar')">
                                <v-icon color="warning" medium>edit</v-icon>
                            </v-btn>
                            <v-btn flat icon  @click.stop="editarForumConfirmacao(props.item, 'eliminar')">
                                <v-icon color="error" medium>delete_forever</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </template>
            </v-data-table>
            <div class="modal fade" id="elimianarForumModal" tabindex="-1" role="dialog" aria-labelledby="elimianarForumModal"
                aria-hidden="true" data-keyboard="false" data-backdrop="static" max-with="500">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmacaoEmailModal">Confirmação</h5>
                            <button type="button" @click="fecharEliminarModal()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-card>
                            <v-card-title>Tem a certeza que que apagar o fórum?</v-card-title>
                            <v-container fluid grid-list-md v-if="this.$store.state.user && this.$store.state.user.tipo === 'admin' && !this.forumDoAdmin">
                                <v-layout row wrap>
                                    <v-flex xs12 sm9 d-flex>
                                        <v-textarea
                                            v-model="justificaçao"
                                            label="justificaçao para apagar este fórum"
                                            no-resize
                                        ></v-textarea>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" flat="flat" @click="fecharEliminarModal()" :disabled="isLoading">
                                    Não
                                </v-btn>
                                <v-btn color="green darken-1" flat="flat" @click="eliminar()" :disabled="isLoading">Sim</v-btn>
                            </v-card-actions>
                        </v-card>
                    </div>
                </div>
            </div>
        </v-app>
        <br><br>
    </div>
</template>

<script>
    import AddEditForum from './adicionarEditarForum.vue'
    import confirmacaoEmail from './confirmacaoEmail.vue'

    export default {
        components: {
            'forum-add-edit': AddEditForum,
            'confirmacao-email': confirmacaoEmail
        },
        created() {
            this.getForums();
            this.getPatrimonios();
            if (this.$store.state.user){
                this.getToken();
                this.emailAtual = this.$store.state.user.email;
            }
        },
        data() {
            return {
                pagination: {
                    descending: true,
                    page: 10,
                    rowsPerPage: 10,
                    sortBy: 'numComentarios',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 25]
                },
                search: '',
                headers: [
                    {text: 'Titulo', value: 'titulo'},
                    {text: 'Número de comentários', value: 'numComentarios'},
                    {text: 'Ultimo comentário', value: 'data_ultima_atualizacao_comentario'},
                    {text: 'Ações', value: 'acoes', sortable: false}
                ],
                forums: [],
                forumAtual: {},
                emailAtual: '',
                codigoAtual: '',
                confirmacaoTipo: '',
                patrimoniosSelected: [],
                patrimonios: [],
                forumDoAdmin: false,
                justificaçao: '',
                isLoading: true,
            }
        },
        methods: {
            getForums() {
                this.isLoading= true;
                axios.get('/api/forums')
                    .then(response => {
                        this.forums = response.data.data;
                        this.isLoading= false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading= false;
                    });
            },
            getPatrimonios(){
                this.isLoading= true;
                axios.get('/api/patrimoniosShort')
                    .then(response => {
                        this.patrimonios = response.data.data;
                        this.isLoading= false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading= false;
                    });
            },
            getToken(){
                this.isLoading= true;
                axios.get('/api/users/me/token').then(response => {
                    this.codigoAtual = response.data.data;
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            showEdit(forum) {
                if (this.$store.state.user){
                    this.isLoading= true;
                    axios.post('/api/forums/' + this.forum.id + '/compararEmails', this.forum).then(response => {
                        this.toastPopUp("success", "Enviámos-lhe um email com o código de acesso");
                        this.emailEnviado = true;
                        this.isLoading= false;
                    }).catch(error => {
                        this.isLoading= false;
                        this.toastErrorApi(error);
                    })
                }
                $('#addForumModal').modal('show');
            },
            showForum(forum) {
                this.$router.push({path: '/forums/' + forum.id, params: {'forum': forum}});
            },
            editarForumConfirmacao(forum, tipo){
                this.forumAtual = Object.assign({}, forum);
                this.forumAtual.codigo = this.codigoAtual;
                this.confirmacaoTipo = tipo;
                if (this.$store.state.user){
                    this.isLoading= true;
                    axios.post('/api/forums/' + forum.id + '/compararEmails', {'user_email': this.$store.state.user.email, 'tipo': tipo}).then(response => {
                        this.isLoading= false;
                        if (response.data.tipo && response.data.tipo == 'forumDoAdmin'){
                            this.forumDoAdmin = true;
                        }
                        this.emailConfirmado();
                    }).catch(error => {
                        this.isLoading= false;
                        this.toastErrorApi(error);
                    });
                } else{
                    $('#confirmacaoEmailModal').modal('show');
                }
            },
            eliminar(){
                this.isLoading= true;
                let header;
                let url = '/api/forums/' + this.forumAtual.id + '?codigo=' + this.codigoAtual + '&user_email=' + this.emailAtual;
                if (this.$store.state.user){
                    url = '/api/forums/' + this.forumAtual.id + '?user_id=' + this.$store.state.user.id;
                    header = {headers: {Authorization: this.codigoAtual, 'Content-Type': 'application/json'}};
                    if (this.$store.state.user.tipo == 'admin' && this.justificaçao.length > 0){
                        url = '/api/forums/' + this.forumAtual.id + '?user_id=' + this.$store.state.user.id + '&justificaçao=' + this.justificaçao;
                    }
                }
                axios.delete(url, header).then(response => {
                    this.isLoading= false;
                    this.justificaçao = '';
                    this.forumDoAdmin = false;
                    $('#elimianarForumModal').modal('hide');
                    this.toastPopUp("success", "Fórum Apagado!");
                    this.getForums();
                }).catch(error => {
                    this.forumDoAdmin = false;
                    this.isLoading= false;
                    this.toastErrorApi(error);
                });
            },
            fecharEliminarModal(){
                this.cleanForumAtual();
                this.forumDoAdmin = false
                $('#elimianarForumModal').modal('hide');
            },
            emailConfirmado(){
                if (this.confirmacaoTipo == 'editar'){
                    this.forumDoAdmin = false;
                    setTimeout(function() { $('#addForumModal').modal('show'); }, 500);//sem o delay o modal perde o scroll
                } else{
                    $('#elimianarForumModal').modal('show');
                }
            },
            receberCredenciais(email, codigo){
                this.emailAtual = email;
                this.forumAtual.user_email = email;
                this.codigoAtual = codigo;
                this.forumAtual.codigo = codigo;
            },
            cleanForumAtual(){
                this.forumAtual = Object.assign({}, null);
            }
        },
        computed: {
            filteredForums() {
                return this.forums.filter((f) => {
                    return this.patrimoniosSelected.length === 0 || this.patrimoniosSelected.length === this.patrimonios.length ||
                        f.patrimonios.some(patrim => this.patrimoniosSelected.includes(patrim.nome));
                });
            }
        }
    }
</script>

<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <forum-add-edit :forum="forumAtual" :patrimonios="patrimonios" v-on:getFor="getForums()"></forum-add-edit>
            <confirmacao-email :forum="forumAtual" v-on:emailConfirmado="emailConfirmado()" v-on:codigo="receberCredenciais"></confirmacao-email>
            <h3>Fórum / Pesquisa</h3>
            <br>
            <v-card append float>
                <v-card-title>
                    <v-container fluid grid-list-md>
                        <v-layout row wrap>
                            <v-flex xs12 sm4>
                                <v-text-field
                                    v-model="search"
                                    append-icon="pesquisa"
                                    label="Pesquisar em todos os campos"
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
                    <tr @click="showForum(props.item)">
                        <td class="text-xs-left">{{ props.item.titulo }}</td>
                        <td class="text-xs-left">{{ props.item.comentarios.length }}</td>
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
                    page: 1,
                    rowsPerPage: 20,
                    sortBy: 'comentarios.length',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20, 50]
                },
                search: '',
                headers: [
                    {text: 'Titulo', value: 'titulo'},
                    {text: 'Número de comentários', value: 'comentarios.length'},
                    {text: 'Ações', value: 'acoes', sortable: false}
                ],
                forums: [],
                forumAtual: {},
                emailAtual: '',
                codigoAtual: '',
                confirmacaoTipo: '',
                patrimoniosSelected: [],
                patrimonios: [],
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
                //this.$router.push({path: '/forum/' + forum.id, params: {'forum': forum}});
            },
            editarForumConfirmacao(forum, tipo){
                let forumTemp = Object.assign({}, forum);
                forumTemp.codigo = this.codigoAtual;
                this.forumAtual = forumTemp;
                this.confirmacaoTipo = tipo;
                if (this.$store.state.user){
                    this.isLoading= true;
                    axios.post('/api/forums/' + forum.id + '/compararEmails', {'user_email': this.$store.state.user.email}).then(response => {
                        this.isLoading= false;
                        this.emailConfirmado();
                    }).catch(error => {
                        this.isLoading= false;
                        this.toastErrorApi(error);
                    });
                } else{
                    $('#confirmacaoEmailModal').modal('show');
                }
            },
            emailConfirmado(){
                if (this.confirmacaoTipo == 'editar'){
                    setTimeout(function() { $('#addForumModal').modal('show'); }, 500);//sem o delay o modal perde o scroll
                } else{//delete
                    this.dialog = false;
                    let header;
                    let url = '/api/forums/' + this.forumAtual.id + '?codigo=' + this.codigoAtual + '&user_email=' + this.emailAtual;
                    if (this.$store.state.user){
                        url = '/api/forums/' + this.forumAtual.id + '?user_id=' + this.$store.state.user.id;
                        header = {headers: {Authorization: this.codigoAtual, 'Content-Type': 'application/json'}};
                    }
                    axios.delete(url, header).then(response => {
                        this.toastPopUp("success", "Fórum Apagado!");
                        this.getForums();
                    }).catch(error => {
                        this.toastErrorApi(error);
                    });
                }
            },
            receberCredenciais(email, codigo){
                this.emailAtual = email;
                this.codigoAtual = codigo;
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

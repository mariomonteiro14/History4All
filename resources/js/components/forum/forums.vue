<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <forum-add-edit :forum="forumAtual" :patrimonios="patrimonios" v-on:getFor="getForums()"
                v-on:credenciais="setCredenciais" v-on:clean="cleanForumAtual"></forum-add-edit>
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
                            <v-speed-dial
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
                                            small
                                            text
                                            color="warning"
                                            @click="editarForumConfirmacao(props.item, 'editar')"
                                        >
                                            editar
                                        </v-btn>
                                        <v-btn
                                            small
                                            class="white--text"
                                            color="red"
                                            @click="editarForumConfirmacao(props.item, 'eliminar')"
                                        >
                                            Eliminar
                                        </v-btn>
                                        <v-btn
                                            small
                                            class="white--text"
                                            color="indigo"
                                            @click="showDenuncia(props.item.id)"
                                            v-if="!$store.state.user || ($store.state.user && $store.state.user.tipo!='admin')"
                                        >
                                            Denunciar
                                        </v-btn>
                                    </v-layout>
                                </v-card>
                            </v-speed-dial>
                        </td>
                    </tr>
                </template>
            </v-data-table>
            <!--ELIMINAR-->
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
        <!--CONFIRMAÇÃO EMAIL-->
        <v-layout justify-center>
            <v-dialog v-model="dialogCode" width="450px">
                    <v-card>
                        <v-card-title class="headline">Verificação de email</v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-layout class="form-group" row align-center>
                                    <v-text-field id="inputEmail"
                                        v-model="email"
                                        label="Email"
                                        :rules="emailRules"
                                        clearable
                                        required
                                    ></v-text-field>
                                    <v-text-field class="text-sm-left" v-if="emailEnviado"
                                        v-model="codigo"
                                        label="Código de acesso"
                                        :rules="[v => !!v || 'Código é obrigatório']"
                                        clearable
                                        :disabled="isLoading"
                                    >
                                        <template v-slot:append v-if="!codigo">
                                            <v-tooltip left>
                                                <template v-slot:activator="{ on }">
                                                    <v-icon v-on="on">help</v-icon>
                                                </template>
                                                Insira o seu código de acesso que lhe foi enviado por email
                                            </v-tooltip>
                                        </template>
                                    </v-text-field>
                                </v-layout>
                            </v-container>
                        </v-card-text>
                        <v-card-actions v-if="!isLoading">
                            <v-spacer></v-spacer>
                            <v-btn color="green darken-1" flat text @click="gerarCodigo" :disabled="isLoading">{{!emailEnviado ? 'Enviar' : 'Reenviar'}} código
                                <template v-slot:append v-if="!codigo">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon v-on="on">help</v-icon>
                                        </template>
                                        Clique aqui para reenviar o email com o código
                                    </v-tooltip>
                                </template>
                            </v-btn>
                            <v-btn
                                color="orange darken-1"
                                text flat
                                @click="confirmarCodigo"
                                v-if="emailEnviado"
                                :disabled="!codigo"
                            >
                                Confirmar código
                            </v-btn>
                            <v-btn
                                color="red darken-1"
                                text flat
                                @click="dialogCode = false"
                            >
                                Cancelar
                            </v-btn>
                        </v-card-actions>
                        <v-card-actions v-else>
                            <v-spacer></v-spacer>
                            <loader style="padding: 10px" color="green"
                                    size="32px"></loader>
                        </v-card-actions>
                    </v-card>
            </v-dialog>
        </v-layout>
        <br><br>
        <denuncia-dialog ref="denunciarModal" :isForum="true" :id="forumAtual"></denuncia-dialog>
    </div>
</template>

<script>
    import AddEditForum from './adicionarEditarForum.vue'
    import DenunciaModal from './denunciarModal';

    export default {
        components: {
            'forum-add-edit': AddEditForum,
            'denuncia-dialog': DenunciaModal,
        },
        created() {
            this.getForums();
            this.getPatrimonios();
            if (this.$cookies.isKey("credentials")) {
                this.credenciais = this.$cookies.get("credentials");
            }
            if (this.$store.state.user){
                this.credenciais.email = this.$store.state.user.email;
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
                credenciais: {
                    email: null,
                    codigo: null
                },
                forums: [],
                forumAtual: {},
                email: '',
                codigo: '',
                confirmacaoTipo: '',
                patrimoniosSelected: [],
                patrimonios: [],
                forumDoAdmin: false,
                justificaçao: '',
                isLoading: true,
                dialogCode: false,
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                emailEnviado: false,
                dialogPodeContinuar: false,
                fab: false,
                fabId: 0
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
            showForum(forum) {
                this.$router.push({path: '/forums/' + forum.id, params: {'forum': forum}});
            },
            editarForumConfirmacao(forum, tipo){
                this.forumAtual = Object.assign({}, forum);
                this.forumAtual.email = this.credenciais.email;
                this.forumAtual.codigo = this.credenciais.codigo;
                this.confirmacaoTipo = tipo;
                if (this.$store.state.user || this.credenciais.codigo){
                    let dados = {
                        'user_email': this.credenciais.email,
                        'codigo': this.credenciais.codigo,
                        'tipo': tipo
                    }
                    this.isLoading= true;
                    axios.post('/api/forums/' + forum.id + '/compararEmails', dados).then(response => {
                        this.isLoading= false;
                        if (response.data.tipo && response.data.tipo == 'forumDoAdmin'){
                            this.forumDoAdmin = true;
                        }
                        this.emailConfirmado();
                    }).catch(error => {
                        this.cleanForumAtual();
                        this.isLoading= false;
                        this.toastErrorApi(error);
                    });
                } else{
                    this.dialogCode = true;//confirmação de email
                }
            },
            eliminar(){
                this.isLoading= true;
                let url = '/api/forums/' + this.forumAtual.id + '?codigo=' + this.credenciais.codigo + '&user_email=' + this.credenciais.email;
                if (this.$store.state.user){
                    url = '/api/forums/' + this.forumAtual.id;
                    if (this.$store.state.user.tipo == 'admin' && this.justificaçao.length > 0){
                        url = '/api/forums/' + this.forumAtual.id + '?justificaçao=' + this.justificaçao;
                    }
                }
                axios.delete(url).then(response => {
                    this.isLoading= false;
                    this.justificaçao = '';
                    this.forumDoAdmin = false;
                    this.cleanForumAtual();
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
                this.forumDoAdmin = false;
                $('#elimianarForumModal').modal('hide');
            },
            emailConfirmado(){
                this.dialogPodeContinuar = true;
                if (this.confirmacaoTipo == 'editar'){
                    this.forumDoAdmin = false;
                    this.forumAtual.email = this.credenciais.email;
                    this.forumAtual.codigo = this.credenciais.codigo;
                    $('#addForumModal').modal('show');
                } else{
                    $('#elimianarForumModal').modal('show');
                }
            },
            cleanForumAtual(){
                this.$cookies.set("credentials", this.credenciais, "1d");
                this.dialogPodeContinuar = false;
                this.forumAtual = Object.assign({}, {
                    "id": undefined,
                    "titulo": "",
                    "descricao": "",
                    "user_email": "",
                    "patrimonios": []
                });
            },
            setCredenciais(email, codigo){
                this.credenciais.email = email;
                this.credenciais.codigo = codigo;
                this.$cookies.set("credentials", this.credenciais, "1d");
            },
            gerarCodigo(){
                if (this.$store.state.user && !this.credenciais.email){
                    this.credenciais.email = this.$store.state.user.email;
                }
                this.isLoading= true;
                axios.post('/api/forums/' + this.forumAtual.id + '/compararEmails', {'user_email': this.email}).then(response => {
                    this.toastPopUp("success", "Enviámos-lhe um email com o código de acesso");
                    this.emailEnviado = true;
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            confirmarCodigo() {
                axios.post('/api/forums/compararCodigo', {'user_email': this.email, 'codigo': this.codigo}).then(response => {
                    this.isLoading= false;
                    this.credenciais.email = this.email;
                    this.credenciais.codigo = this.codigo;
                    this.$cookies.set("credentials", {'email': this.email, 'codigo': this.codigo}, "1d");
                    this.emailConfirmado();
                    this.dialogCode = false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            showDenuncia(forumId) {
                this.forumAtual = forumId;
                this.$refs.denunciarModal.show = true;
            },
            changeFabId(id) {
                if (!this.fab && this.fabId == id) {
                    this.fabId = 0;
                }
                if (this.fabId != id) {
                    this.fabId = id;
                }
            },
        },
        computed: {
            filteredForums() {
                return this.forums.filter((f) => {
                    return this.patrimoniosSelected.length === 0 || this.patrimoniosSelected.length === this.patrimonios.length ||
                        f.patrimonios.some(patrim => this.patrimoniosSelected.includes(patrim.nome));
                });
            }
        },
        watch: {
            dialogCode (){
                if (this.dialogCode == false && !this.dialogPodeContinuar && (this.confirmacaoTipo == "eliminar" || !this.credenciais.codigo || !this.credenciais.email)){
                    this.cleanForumAtual();
                }
            }
        }
    }
</script>

<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>{{titulo}}</h3>
            <br>
            <v-card append float>
                <v-card-title>
                    <v-container fluid grid-list-xl>
                        <v-layout row wrap align-center>
                            <v-flex xs12 sm3 d-flex>
                                <v-select
                                    v-model="tipoUser"
                                    :items="usersTipo"
                                    label="Filtrar por tipo"
                                    class="input-group--focused"
                                ></v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-flex xs12 sm3 d-flex>
                                <v-text-field
                                    v-model="search"
                                    append-icon="pesquisa"
                                    label="Pesquisa"
                                    single-line
                                    hide-details
                                    clearable
                                ></v-text-field>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-flex xs12 sm4 d-flex>
                                <v-layout>
                                    <v-btn color="success" round data-toggle="modal" data-target="#addUserModal">
                                        <v-icon class="material-icons">add</v-icon>
                                        &nbsp Utilizador
                                    </v-btn>
                                    <v-btn round v-if="trashed" color="warning" @click="changeUsers">Utilizadores Ativos
                                    </v-btn>
                                    <v-btn round v-else color="warning" @click="changeUsers">Utilizadores
                                        Eliminados
                                    </v-btn>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-title>
            </v-card>
            <v-data-table :headers="headers" :items="filteredUsers" :search="search" class="elevation-1"
                          :pagination.sync="pagination" :expand="expand" :loading="isLoading">
                <template v-slot:items="props">
                    <tr v-bind:class="colorUserType(props.item.tipo)">
                        <td class="text-xs-center">
                            <v-avatar size="90px" color="white">
                                <img v-if="props.item.foto && props.item.tipo !='aluno'"
                                     v-bind:src="getUserPhoto(props.item.foto)"/>
                                <v-icon v-else class="indigo--text" large>far fa-user</v-icon>
                            </v-avatar>
                        </td>
                        <td class="text-xs-left">
                            <strong v-if="props.item.tipo !='aluno'">{{ props.item.nome }}</strong>
                            <span v-else>(confidencial)</span>
                        </td>
                        <td class="text-xs-left">{{ props.item.email }}</td>
                        <!-- Se Filtro for Todos -->
                        <td v-if="tipoUser=='Todos'" class="text-xs-center">{{ props.item.tipo }}</td>

                        <!-- Se Filtro for Professor ou Alunos -->
                        <td v-if="tipoUser=='professor' || tipoUser=='aluno'" class="text-xs-center">{{
                            props.item.escola[0] }}
                        </td>
                        <td v-if="tipoUser=='Todos' || (tipoUser=='professor' && props.item.turmas.length > 0)" class="text-sm-right">
                            <v-btn @click="props.expanded = !props.expanded" icon>
                                <v-icon color="brown darken-1" medium>info</v-icon>
                            </v-btn>
                        </td>
                        <td v-if="tipoUser=='professor' && props.item.turmas.length == 0" class="text-sm-right"/>
                        <!-- Se Filtro for Alunos -->
                        <td v-if="tipoUser=='aluno' &&  props.item.turma[0]" class="text-xs-center">{{
                            props.item.turma[0] }}
                        </td>
                        <td v-if="tipoUser=='aluno' &&  !props.item.turma[0]" class="text-xs-center"> ---</td>

                        <td class="text-xs-center"
                            v-if="!trashed && $store.state.user.id != props.item.id && !editingUsers.includes(props.item.id)">

                            <v-btn icon v-if="props.item.tipo =='professor'" @click="showEdit(props.item)">
                                <v-icon color="warning" medium>edit</v-icon>
                            </v-btn>
                            <v-btn icon v-if="props.item.tipo !='aluno'" @click.stop="showDeleteVerif(props.item.id)">
                                <v-icon color="error" medium>delete_forever</v-icon>
                            </v-btn>

                        </td>
                        <td class="" v-if="editingUsers.includes(props.item.id)">
                            <center>
                                <loader color="green" size="32px"></loader>
                            </center>
                        </td>
                        <td v-if="$store.state.user.id == props.item.id"></td>
                        <td class="text-xs-center" v-if="trashed && !editingUsers.includes(props.item.id)">
                            <v-btn round color="warning" @click="restaurarUser(props.item)">
                                Restaurar
                            </v-btn>
                            <v-btn icon flat @click.stop="showDeleteVerif(props.item.id)">
                                <v-icon color="error" medium>delete_forever</v-icon>
                            </v-btn>
                        </td>

                    </tr>
                </template>
                <template v-slot:expand="props">
                    <v-card flat v-bind:class="colorUserType">
                        <v-card-text class="brown lighten-3">{{getDetail(props.item)}}
                        </v-card-text>
                    </v-card>
                </template>
            </v-data-table>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Tem a certeza que que elimiar o utilizador?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagar()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <br><br>
        <add-user show-type="true" :user="userForm" v-on:getUsers="getUsers"></add-user>


        <div @focusout="closeLists">
            <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModal"
                 aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModal">Editar Utilizador</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    :disabled="aEnviar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container box">
                                <div class="form-group" v-if="userAtual.tipo != 'admin'" @click="setOpenList">
                                    <v-select
                                        ref="selectE"
                                        label="Escola"
                                        v-model="userAtual.escola"
                                        :items="escolas"
                                        item-text="nome"
                                        :rules="[v => !!v || 'Escola é obrigatório']"
                                        class="input-group--focused"
                                        required
                                    ></v-select>
                                </div>
                                <span v-if="userAtual.tipo == 'professor' && userAtual.turmas && userAtual.turmas.length > 0">
                                    Se alterar a escola, a(s) turma(s) associada(s) a este professor ({{getProfessorTurmasText}}) ficaram sem professor.
                                </span>
                                <div class="form-group" v-if="userAtual.tipo == 'aluno' && userAtual.escola"
                                     @click="setOpenList">
                                    <v-select
                                        ref="selectT"
                                        label="Turma"
                                        v-model="userAtual.turma"
                                        :items="turmas"
                                        item-text="nome"
                                        class="input-group--focused"
                                        clearable
                                        required
                                        @click:clear="userAtual.turma=''"
                                    ></v-select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" v-if="!aEnviar">
                            <button class="btn btn-info" v-on:click.prevent="saveEdit"
                                    :disabled="!userAtual.escola">Guardar
                            </button>
                        </div>
                        <div v-else class="modal-footer">
                            <loader color="green" size="32px"></loader>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
    import BRow from "bootstrap-vue/src/components/layout/row";

    import AddUser from './adicionarUser.vue';

    export default {
        components: {
            BRow,
            'add-user': AddUser,
        },
        created() {
            this.getUsers();
            this.getEscolas();
        },
        data() {
            return {
                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 10,
                    sortBy: 'tipo',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                search: '',
                expand: false,
                tipoUser: 'Todos',
                usersTipo: ['Todos', 'admin', 'professor', 'aluno'],
                users: [],
                userAApagar: '',
                dialog: false,
                trashed: false,
                userForm: {
                    nome: '',
                    email: '',
                    tipo: '',
                    escola: '',
                    turma: '',
                },
                userAtual: {},
                userDetail: {},
                escolas: [],
                selAberto: false, //atributo pra corrigir bug no modal
                isLoading: true,
                aEnviar: false,
                editingUsers: []
            }
        },
        methods: {
            getUsers(removeUserId = 0) {
                this.isLoading = true;
                axios.get('/api/users')
                    .then(response => {
                        this.users = response.data.data;
                        this.trashed = false;
                        this.isLoading = false;
                        if (removeUserId > 0) {
                            this.removeUserEditing(removeUserId);
                        }
                    }).catch(error => {
                    this.toastErrorApi(error);
                    this.isLoading = false;
                    if (removeUserId > 0) {
                        this.removeUserEditing(removeUserId);
                    }

                });
            },

            getUsersTrashed(removeUserId = 0) {
                this.isLoading = true;
                axios.get('/api/usersTrashed')
                    .then(response => {
                        if (!response.data.data.length) {
                            if(this.trashed) {
                                this.users = [];
                            }
                            this.toastPopUp('show', 'Nao existem utilizadores eliminados');
                            if (this.trashed) {
                                this.changeUsers();
                            }
                        } else {
                            this.users = response.data.data;
                            this.trashed = true;
                        }
                        if (removeUserId > 0) {
                            this.removeUserEditing(removeUserId);
                        }
                        this.isLoading = false;
                    }).catch(error => {
                    this.toastErrorApi(error);
                    if (removeUserId > 0) {
                        this.removeUserEditing(removeUserId);
                    }
                    this.isLoading = false;

                });
            },
            changeUsers() {
                if (this.trashed) {
                    this.getUsers();
                } else {
                    this.getUsersTrashed();
                }
                this.editingUsers = [];
            },

            getDetail(user) {
                if (user.tipo == "admin") {
                    return "Administrador do History4All"
                }
                if (user.tipo == "professor") {
                    if (this.tipoUser == 'Todos') {
                        let string = "Professor da " + user.escola[0] + ".";
                        if (user.turmas && user.turmas.length > 0) {
                            if (user.turmas.length == 1) {
                                string = string + " Leciona a turma " + user.turmas[0] + ".";
                            } else {
                                string = string + " Leciona as turmas ";
                                for (let i = 0; i < user.turmas.length; i++) {
                                    string = string + user.turmas[i];
                                    if (i < user.turmas.length - 1) {
                                        string = string + ", "
                                    }
                                }
                                string = string + ".";
                            }
                        }
                        return string;
                    }else{
                        if (user.turmas && user.turmas.length > 0) {
                            let string = "";
                            if (user.turmas.length == 1) {
                                 string = " Leciona a turma " + user.turmas[0] + ".";
                            } else {
                                 string = " Leciona as turmas ";
                                for (let i = 0; i < user.turmas.length; i++) {
                                    string = string + user.turmas[i];
                                    if (i < user.turmas.length - 1) {
                                        string = string + ", "
                                    }
                                }
                                string = string + ".";
                            }

                            return string;
                        }
                        return;
                    }
                }
                if (user.tipo == "aluno" && user.turma[0]) {
                    return "Aluno da " + user.escola[0] + ", turma " + user.turma[0];
                }
                return "Aluno da " + user.escola[0] + ", sem turma";
            },

            showEdit(user) {
                this.userAtual = Object.assign({}, user);
                this.userAtual.turma = this.userAtual.turma[0];
                this.userAtual.escola = this.userAtual.escola[0];
                $('#editUserModal').modal('show');

            },
            showDeleteVerif(user_id) {
                this.dialog = true;
                this.userAApagar = user_id;
            },
            apagar() {
                this.dialog = false;
                this.editingUsers.push(this.userAApagar);
                axios.delete('/api/users/' + this.userAApagar)
                    .then(response => {
                        this.toastPopUp("success", "Utilizador Apagado!");
                        if (this.trashed == true) {
                            this.getUsersTrashed(this.userAApagar);
                        } else {
                            this.getUsers(this.userAApagar);
                        }
                    }).catch(error => {
                    this.toastErrorApi(error);
                    this.removeUserEditing(this.userAApagar);

                });
            },
            saveEdit() {
                this.aEnviar = true;
                this.editingUsers.push(this.userAtual.id);
                axios.put('/api/users/' + this.userAtual.id, this.userAtual)
                    .then(response => {
                        this.aEnviar = false;
                        this.toastPopUp("success", "Utilizador Atualizado");
                        this.$socket.emit('atualizar_notificacoes', this.userAtual.id);
                        this.getUsers(this.userAtual.id);
                        $('#editUserModal').modal('hide');
                    }).catch(error => {
                    this.toastErrorApi(error);
                    this.removeUserEditing(this.userAtual.id);
                    this.aEnviar = false;
                });
            },
            restaurarUser(user) {
                this.editingUsers.push(user.id);
                axios.put('/api/users/restaurar/' + user.id).then(res => {
                    this.toastPopUp("success", "Uilizador restaurado");
                    this.$socket.emit('atualizar_notificacoes', user.id);
                    this.getUsersTrashed(user.id);
                }).catch(error => {
                    this.removeUserEditing(user.id);
                    this.toastErrorApi(error);
                    console.log(error);
                });

            },
            colorUserType: function (tipo) {
                if (tipo == "admin") {
                    return "alert-warning"
                }
                if (tipo == "aluno") {
                    return "alert-primary";
                }

                return "alert-secondary";

            },

            getEscolas: function () {
                axios.get("/api/escolas").then(response => {
                    this.escolas = response.data.data;
                }).catch(error => {
                    this.toastErrorApi(error);
                });
            },
            //Metodos pra corrigir bug nos Modal
            setOpenList() {
                setTimeout(() => {
                    if (this.$refs.selectT && this.$refs.selectT.isMenuActive == true || this.$refs.selectE.isMenuActive == true) {
                        setTimeout(() => {
                            this.selAberto = true;
                        }, 30);
                    }
                }, 10)
            },

            closeLists() {
                if (this.selAberto == true) {
                    this.selAberto = false;
                    this.$refs.selectE.isMenuActive = false;
                    if (this.$refs.selectT) {
                        this.$refs.selectT.isMenuActive = false;
                    }
                }
            },

            removeUserEditing(user_id) {
                //console.log(this.editingUsers);
                for (let i = 0; i < this.editingUsers.length; i++) {
                    if (this.editingUsers[i] == user_id) {
                        this.editingUsers.splice(i, 1);
                        i--;
                    }
                }
                // console.log(this.editingUsers);

            }
            ////////////////////////////////////////////////
        },
        computed: {
            filteredUsers() {
                return this.users.filter((i) => {
                    return (this.tipoUser === 'Todos' || i.tipo === this.tipoUser)
                });
            },
            turmas: function () {
                for (let i in this.escolas) {
                    if (this.escolas[i].nome === this.userAtual.escola) {
                        return this.escolas[i].turmas;
                    }
                }
            },
            titulo: function () {
                let t = "Utilizadores / Gestão "
                if (this.tipoUser == "Todos") {
                    return t;
                }
                if (this.tipoUser == "admin") {
                    return t + "/ Administradores";
                }
                if (this.tipoUser == "professor") {
                    return t + "/ Professores";
                }
                if (this.tipoUser == "aluno") {
                    return t + "/ Alunos";
                }
            },
            getProfessorTurmasText: function () {
                if (this.userAtual.turmas.lenght == 0) {
                    return;
                }
                let string = "";
                for (let i = 0; i < this.userAtual.turmas.length; i++) {
                    string = string + this.userAtual.turmas[i];
                    if (i < this.userAtual.turmas.length - 1) {
                        string = string + ", "
                    }
                }
                return string;
            },
            headers: function () {
                if (this.tipoUser == "Todos") {
                    return [
                        {
                            text: '',
                            align: 'left',
                            sortable: false,
                            value: 'image'
                        },
                        {text: 'Nome', value: 'nome'},
                        {text: 'Email', value: 'email'},
                        {text: 'Tipo', value: 'tipo', align: 'center'},
                        {text: '', value: 'info', align: 'right', sortable: false},
                        {text: 'Actions', value: '', sortable: false, align: 'center'},
                    ];
                }
                if (this.tipoUser == "admin") {
                    return [
                        {
                            text: '',
                            align: 'left',
                            sortable: false,
                            value: 'image'
                        },
                        {text: 'Nome', value: 'nome'},
                        {text: 'Email', value: 'email'},
                        {text: 'Actions', value: '', sortable: false, align: 'center'},
                    ];
                }
                if (this.tipoUser == "professor") {
                    return [
                        {
                            text: '',
                            align: 'left',
                            sortable: false,
                            value: 'image'
                        },
                        {text: 'Nome', value: 'nome'},
                        {text: 'Email', value: 'email'},
                        {text: 'Escola', value: 'escola', align: 'center', sortable: true},
                        {text: '', value: 'info', align: 'right', sortable: false},
                        {text: 'Actions', value: '', sortable: false, align: 'center'},
                    ];
                }
                if (this.tipoUser == "aluno") {
                    return [
                        {
                            text: '',
                            align: 'left',
                            sortable: false,
                            value: 'image'
                        },
                        {text: 'Nome', value: 'nome'},
                        {text: 'Email', value: 'email'},
                        {text: 'Escola', value: 'escola', align: 'center', sortable: true},
                        {text: 'Turma', value: 'turma', align: 'center', sortable: true},
                        {text: '', value: '', sortable: false, align: 'center'},
                    ];
                }
            }
        },

        watch: {
            'userAtual.escola': function (newVal, oldVal) {
                this.userAtual.escola = newVal;
                this.userAtual.turma = undefined;
            }
        }
    }
</script>

<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Utilizadores / Gestão</h3>
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
                            <v-text-field
                                v-model="search"
                                append-icon="search"
                                label="Search"
                                single-line
                                hide-details
                                clearable
                            ></v-text-field>
                            <v-spacer></v-spacer>
                            <v-btn color="success" data-toggle="modal" data-target="#addUserModal">Criar utilizador <i
                                class="material-icons">add_box</i>
                            </v-btn>
                            <v-btn v-if="trashed" color="warning" @click="getUsers()">Utilizadores Ativos</v-btn>
                            <v-btn v-else color="warning" @click="getUsersTrashed()">Utilizadores Apagados</v-btn>

                        </v-layout>
                    </v-container>
                </v-card-title>
            </v-card>
            <v-data-table :headers="headers" :items="filteredUsers" :search="search" class="elevation-1"
                          :pagination.sync="pagination">

                <template v-slot:items="props">
                    <tr v-bind:class="colorUserType(props.item.tipo)">
                        <td class="text-xs-center">
                            <v-avatar size="90px" v-if="props.item.foto">
                                <img v-bind:src="getUserPhoto(props.item.foto)"/>
                            </v-avatar>
                        </td>
                        <td class="text-xs-left">{{ props.item.nome }}</td>
                        <td class="text-xs-left">{{ props.item.email }}</td>
                        <td class="text-xs-center">{{ props.item.tipo }}</td>
                        <td class="text-xs-center" v-if="!trashed && $store.state.user.id != props.item.id ">
                            <v-btn v-if="props.item.tipo!='admin'" color="warning" @click="showEdit(props.item)">
                                Editar
                                <v-icon small class="mr-2">edit</v-icon>
                            </v-btn>
                            <v-btn color="error" @click.stop="showDeleteVerif(props.item.id)">
                                Apagar
                                <v-icon small>delete_forever</v-icon>
                            </v-btn>
                        </td>
                        <td v-if="$store.state.user.id == props.item.id"></td>
                        <td class="text-xs-center" v-if="trashed">
                            <v-btn color="warning" @click="restaurarUser(props.item)">
                                Restaurar
                                <v-icon small class="mr-2"></v-icon>
                            </v-btn>
                            <v-btn color="error" @click.stop="showDeleteVerif(props.item.id)">
                                Apagar
                                <v-icon small>delete_forever</v-icon>
                            </v-btn>
                        </td>


                    </tr>
                </template>
            </v-data-table>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Tem acerteza que que elimiar o utilizador?</v-card-text>
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
        <add-user v-on:getUsers="getUsers"></add-user>

        <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModal">Editar Utilizador</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container box">
                            <div class="form-group" v-if="userAtual.tipo != 'admin'">
                                <v-select
                                    fixed
                                    label="Escola"
                                    v-model="userAtual.escola"
                                    :items="escolas"
                                    item-text="nome"
                                    :rules="[v => !!v || 'Escola é obrigatório']"
                                    class="input-group--focused"
                                    required
                                ></v-select>
                            </div>
                            <div class="form-group" v-if="userAtual.tipo == 'aluno' && userAtual.escola">
                                <v-select
                                    fixed
                                    label="Turma"
                                    v-model="userAtual.turma"
                                    :items="turmas"
                                    :rules="[v => !!v || 'Turma é obrigatório']"
                                    class="input-group--focused"
                                    required
                                ></v-select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="saveEdit"
                                :disabled="!userAtual.escola  || (userAtual.tipo=='aluno' && !userAtual.turma)">Guardar</button>
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
                    rowsPerPage: 5,
                    sortBy: 'tipo',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                headers: [
                    {
                        text: '',
                        align: 'left',
                        sortable: false,
                        value: 'image'
                    },
                    {text: 'Nome', value: 'nome'},
                    {text: 'Email', value: 'email'},
                    {text: 'Tipo', value: 'tipo', align: 'center'},
                    {text: 'Actions', value: '', sortable: false, align: 'center'},
                ],
                search: '',
                tipoUser: 'Todos',
                usersTipo: ['Todos', 'admin', 'professor', 'aluno'],
                users: [],
                userAApagar: '',
                dialog: false,
                trashed: false,
                userAtual: {},
                escolas: [],

            }
        },
        methods: {
            getUsers(url = 'api/users') {

                axios.get(url)
                    .then(response => {
                        this.users = response.data.data;
                        this.trashed = false;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },

            getUsersTrashed(url = 'api/usersTrashed') {
                axios.get(url)
                    .then(response => {
                        if (!response.data.data.length) {
                            this.toastPopUp('warning', 'Nao existem utilizadores apagados')
                        } else {
                            this.users = response.data.data;
                            this.trashed = true;
                        }

                    })
                    .catch(errors => {
                        console.log(errors);
                        this.toastPopUp("error", "`${error.response.data.message}`");
                    });
            },

            showEdit($user) {
                this.userAtual = Object.assign({}, $user);
                this.userAtual.escola = this.userAtual.escola[0];
                this.userAtual.turma = this.userAtual.turma[0];
                console.log(this.userAtual);
                $('#editUserModal').modal('show');

            },
            showDeleteVerif(user_id) {
                this.dialog = true;
                this.userAApagar = user_id;
            },
            apagar() {
                this.dialog = false;
                axios.delete('api/users/' + this.userAApagar)
                    .then(response => {
                        this.toastPopUp("success", "Utilizador Apagado!");
                        if (this.trashed == true) {
                            this.getUsersTrashed();
                        } else {
                            this.getUsers();
                        }
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
            },
            saveEdit() {
                axios.post('api/users/' + this.userAtual.id, this.userAtual)
                    .then(response => {
                        this.toastPopUp("success", "Utilizador Atualizado");
                        this.getUsers();
                        $('#editUserModal').modal('hide');
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
            },
            restaurarUser($user) {
                axios.put('api/users/restaurar/' + $user.id).then(res => {
                    this.toastPopUp("success", "Uilizador restaurado");
                    this.getUsersTrashed();
                }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
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
                axios.get("api/escolas").then(response => {
                    this.escolas = response.data.data;
                }).catch(errors => {
                    console.log(errors);
                });
            },
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
        },

        watch:{
            'userAtual.escola' : function (newVal, oldVal){
                this.userAtual.escola = newVal;
                this.userAtual.turma = undefined;
            }
        }
    }
</script>

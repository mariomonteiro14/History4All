<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Utilizadores / Gestão</h3>
            <br>
            <v-btn color="success" data-toggle="modal" data-target="#addUserModal">Criar património <i
                class="material-icons">add_box</i>
            </v-btn>

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
                            ></v-text-field>
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
                        <td class="text-xs-center" v-if="$store.state.user.id != props.item.id">
                            <v-btn color="warning" @click="showEdit(props.item)">
                                Editar
                                <v-icon small class="mr-2">edit</v-icon>
                            </v-btn>
                            <v-btn color="error" @click.stop="showDeleteVerif(props.item.id)">
                                Apagar
                                <v-icon small>delete_forever</v-icon>
                            </v-btn>
                        </td>
                        <td v-else></td>
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
                    {text: 'Actions', value: '', align: 'center'},
                ],
                search: '',
                tipoUser: 'Todos',
                usersTipo: ['Todos', 'admin', 'professor', 'aluno'],
                users: [],
                userAApagar: '',
                dialog: false,
            }
        },
        methods: {
            getUsers(url = 'api/users') {

                axios.get(url)
                    .then(response => {
                        this.users = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },

            showEdit() {

            },
            showDeleteVerif(user_id){
                this.dialog = true;
                this.userAApagar = user_id;
            },
            apagar(){
                this.dialog = false;
                axios.delete('api/users/' + this.userAApagar)
                    .then(response => {
                        this.toastPopUp("success", "Utilizador Apagado!");
                        this.getUsers();
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

            }
        },
        computed: {
            filteredUsers() {
                return this.users.filter((i) => {
                    return (this.tipoUser === 'Todos' || i.tipo === this.tipoUser)
                });
            }
        }
    }
</script>

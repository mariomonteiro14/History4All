<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Escolas | Turmas / Gerir</h3>
            <br>
            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout row align-center>
                        <v-text-field
                            v-model="search"
                            append-icon="search"
                            label="Pesquisar"
                            clearable
                        ></v-text-field>
                        <v-spacer></v-spacer>
                        <v-select
                            v-model="distritoSelected"
                            :items="distritos"
                            label="Filtrar por distrito"
                            class="input-group--focused"
                        ></v-select>
                        <v-spacer></v-spacer>
                        <v-btn round color="success" data-toggle="modal" data-target="#addEscolaModal">
                            <v-icon medium>add</v-icon> &nbsp Escola
                        </v-btn>
                        <v-btn round color="lime darken-3" class="white--text" data-toggle="modal"
                               data-target="#addUserModal">
                            <v-icon medium>add</v-icon> &nbsp Professor
                        </v-btn>
                    </v-layout>
                </v-container>

                <v-data-table :headers="headersEscola" :items="filteredEscolas" :search="search" class="elevation-1"
                              :pagination.sync="pagination" :expand="expand" :loading="isLoading">
                    <template v-slot:items="props">
                        <tr :class="[props.item.id == escolaAtual.id ? 'alert-success' : '' ]">
                            <td class="text-xs-left"><strong>{{ props.item.nome }}</strong></td>
                            <td class="text-xs-left">{{ props.item.distrito }}</td>
                            <td class="text-xs-right">
                                <v-btn round v-if="props.item.turmas && props.item.turmas[0]" color="primary"
                                       @click="showTurmas(props, props.item)">
                                    <v-icon medium>list</v-icon>
                                    &nbsp Turmas
                                </v-btn>
                            </td>
                            <td class="text-xs-center">
                                <v-btn round v-if="props.item.professores[0]" color="lime darken-3" class="white--text"
                                       @click="showProfessores(props.item)">
                                    <v-icon medium>list</v-icon>
                                    &nbsp Professores
                                </v-btn>
                            </td>
                            <td class="justify-center layout px-0">
                                <v-btn round color="success" @click="showCriarTurma(props.item)"><i
                                    class="material-icons">add</i>&nbsp Turma
                                </v-btn>
                                <v-btn icon color="red" @click.stop="apagarVerificacao(props.item, true)">
                                    <v-icon color="white" medium>delete_forever</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                    <template v-slot:expand="props" class="alert-success">
                        <v-data-table :headers="headersTurma" :items="escolaAtual.turmas"
                                      class="alert-success elevation-1"
                                      hide-actions dark>
                            <template v-slot:items="props">
                                <tr class="alert-primary">
                                    <td class="text-xs-left"><strong>{{props.item.nome}}</strong></td>
                                    <td class="text-xs-right">
                                        <div v-if="props.item.professor[0]">
                                            <img v-if="props.item.professor[0].foto" class="zoom" width="30px"
                                                 height="30px"
                                                 v-bind:src="getUserPhoto(props.item.professor[0].foto)"/>

                                            <v-icon v-else class="indigo--text" small>far fa-user</v-icon>
                                        </div>
                                    </td>
                                    <td class="text-xs-center">
                                        <div v-if="props.item.professor[0]">
                                            <a @click="$router.push('/users/'+ props.item.professor[0].id)">
                                                {{props.item.professor[0].nome}}
                                            </a>
                                        </div>
                                        <div v-else> ----------- </div>
                                    </td>
                                    <td class="text-xs-center">{{props.item.numeroAlunos}}</td>
                                    <td class="text-xs-center">{{props.item.ciclo}}</td>
                                    <td class="text-xs-right">
                                        <v-btn icon color="warning" @click="showEditTurma(props.item)">
                                            <v-icon small>edit</v-icon>

                                        </v-btn>
                                        <v-btn icon color="error" @click.stop="apagarVerificacao(props.item, false)">
                                            <v-icon small>delete_forever</v-icon>
                                        </v-btn>
                                    </td>
                                    <td></td>
                                </tr>
                            </template>
                        </v-data-table>
                    </template>
                </v-data-table>
            </v-card>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text v-if="!turmaAtual.id">Se eliminar esta escola, todas as turmas, professores e alunos seram
                    eliminados permanentemente.
                </v-card-text>
                <v-card-text v-else>Se eliminar esta turma, todos alunos ficaram sem turma.</v-card-text>
                <v-card-text>Quer continuar?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn v-if="!turmaAtual.id" color="green darken-1" flat="flat" @click="apagarEscola()">Sim</v-btn>
                    <v-btn v-else color="green darken-1" flat="flat" @click="apagarTurma()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <br><br>
        <criar-escola v-on:getEscolas="atualizarEscolas"></criar-escola>
        <criar-editar-turma ref="addEditTurma" v-bind:escola="escolaAtual" :turma="turmaAtual"
                            v-on:getEscolas="getEscolas"></criar-editar-turma>
        <lista-professores v-on:atualizar="getEscolas" :titulo="escolaAtual.nome + ' - Professores'"
                           :users="escolaAtual.professores"></lista-professores>
        <add-professor ref="addProf" show-type="false" :user="userForm" v-on:getUsers="getEscolas"></add-professor>


    </div>
</template>

<script>
    import listaProfs from './showUsers';
    import criarEscola from './adicionarEscola';
    import criarTurma from './adicionarEditarTurma';
    import AddUser from '../users/adicionarUser.vue';

    export default {
        components: {
            'lista-professores': listaProfs,
            'criar-escola': criarEscola,
            'criar-editar-turma': criarTurma,
            'add-professor': AddUser,
        },
        mounted() {
            if (this.$store.state.user.tipo === 'admin'){
                this.getEscolas();
            }
        },
        data() {
            return {
                expand: false,
                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 10,
                    sortBy: 'nome',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                distritoSelected: 'Todos',
                distritos: ['Todos', 'Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                search: '',
                headersEscola: [
                    {text: 'Nome', value: 'nome'},
                    {text: 'Distrito', value: 'distrito'},
                    {text: '', align: 'right', value: 'turmas', sortable: false},
                    {text: '', align: 'center', value: 'turmas', sortable: false},
                    {text: 'Ações', align: 'center', value: 'acoes', sortable: false},
                ],
                headersTurma: [
                    {text: 'Nome', value: 'nome', align: 'left'},
                    {text: '', align: 'right', sortable: false},
                    {text: 'Professor', align: 'center', value: 'professor'},
                    {text: 'Numero de Alunos', align: 'center', value: 'numero'},
                    {text: 'Ciclo', align: 'center', value: 'ciclo'},
                    {text: '', align: '', value: 'acoes', sortable: false},
                    {text: '', align: 'center', value: '', sortable: false}
                ],
                escolas: [],
                dialog: false,
                escolaAtual: {},
                turmaAtual: {},
                isLoading: true,
                userForm: {
                    nome: '',
                    email: '',
                    tipo: 'professor',
                    escola: '',
                    turma: '',
                },
            }
        },
        methods: {
            getEscolas(url = '/api/escolas') {
                this.isLoading = true;
                axios.get(url)
                    .then(response => {
                        this.escolas = response.data.data;
                        if (this.escolaAtual.id) {
                            this.escolaAtual = this.escolas.find(e => e.id === this.escolaAtual.id);
                        }
                        this.isLoading = false;
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                        this.isLoading = false;
                    });
            },
            atualizarEscolas(){
                this.getEscolas();
                this.$refs.addProf.getEscolas();
            },

            showTurmas(props, escola) {
                props.expanded = !props.expanded;
                if (!this.escolaAtual.id || this.escolaAtual.id != escola.id) {
                    this.escolaAtual = escola;
                } else {
                    this.escolaAtual = {};
                }
            },
            showProfessores(escola) {
                this.escolaAtual = escola;
                $('#turmaAlunosModal').modal('show');
            },
            showCriarTurma(escola) {
                this.escolaAtual = escola;
                this.turmaAtual = {};
                $('#addTurmaModal').modal('show');
            },
            showEditTurma(turma) {
                this.turmaAtual = Object.assign({}, turma);
                this.turmaAtual.professor = this.turmaAtual.professor[0];
                if (turma.alunos) {
                    this.turmaAtual.alunos = turma.alunos.map(a => ({...a}));
                }
                $('#addTurmaModal').modal('show');
            },

            apagarVerificacao(item, escola_boolean) {
                this.dialog = true;
                if (escola_boolean == true) {
                    this.escolaAtual = item;
                } else {
                    this.turmaAtual = item;
                }
            },

            apagarEscola() {
                this.dialog = false;
                axios.delete('/api/escolas/' + this.escolaAtual.id)
                    .then(response => {
                        this.toastPopUp("success", "Escola Apagado!");
                        this.atualizarEscolas();
                    }).catch(error => {
                        this.toastErrorApi(error);
                });
                this.escolaAtual = {};
            },

            apagarTurma() {
                this.dialog = false;
                axios.delete('/api/escolas/turmas/' + this.turmaAtual.id)
                    .then(response => {
                        this.toastPopUp("success", "Turma Apagado!");
                        this.getEscolas();
                        this.$refs.addEditTurma.getAlunos();
                        if (this.escolaAtual.turmas.length === 1) {
                            this.escolaAtual = {};
                        }
                    }).catch(error => {
                        this.toastErrorApi(error);
                });
                this.turmaAtual = {};
            },
        },
        computed: {
            filteredEscolas() {
                return this.escolas.filter((i) => {
                    return (this.distritoSelected === 'Todos' || i.distrito === this.distritoSelected);
                });
            }
        }
    }
</script>

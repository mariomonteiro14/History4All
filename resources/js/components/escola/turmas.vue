<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Turmas / Gerir</h3>
            <br>
            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout>
                        <v-btn round color="success" data-toggle="modal" data-target="#addUserModal">
                            <v-icon class="material-icons">add</v-icon>
                            &nbsp Aluno
                        </v-btn>
                    </v-layout>
                </v-container>
                <v-container fluid grid-list-xl>
                    <v-layout row align-center>
                        <v-spacer></v-spacer>
                        <v-flex xs12 sm3 d-flex>
                            <v-select
                                v-model="tipoSelected"
                                :items="tipos"
                                label="Turmas"
                                class="input-group--focused"
                            ></v-select>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-flex xs12 sm3 d-flex>
                            <v-select
                                v-model="ciclosSelected"
                                :items="ciclos"
                                chips
                                label="Filtrar por ciclos"
                                multiple
                            ></v-select>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-flex xs12 sm3 d-flex>
                            <v-text-field
                                v-model="search"
                                append-icon="pesquisa"
                                label="Pesquisar"
                                single-line
                                hide-details
                                clearable
                            ></v-text-field>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-flex xs12 sm2 d-flex>
                            <v-btn round color="success" @click="showCriarTurma">
                                <v-icon class="material-icons">add</v-icon>
                                &nbsp Turma
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>

                <v-data-table :headers="headers" :items="filteredTurmas" :search="search" class="elevation-1"
                              :pagination.sync="pagination" :loading="isLoadingTurmas">
                    <template v-slot:items="props">
                        <tr class="alert-primary">
                            <td class="text-xs-left"><strong>{{props.item.nome}}</strong></td>
                            <td class="text-xs-right">
                                <div v-if="props.item.professor[0]">
                                    <img v-if="props.item.professor[0].foto" class="zoom" width="30px" height="30px"
                                         v-bind:src="getUserPhoto(props.item.professor[0].foto)"/>
                                    <v-icon v-else class="indigo--text" small>far fa-user</v-icon>
                                </div>

                            </td>
                            <td class="text-xs-center">
                                <div v-if="props.item.professor[0]">
                                    <a class="indigo--text darken-4"
                                       @click="$router.push('/users/'+ props.item.professor[0].id)">
                                        {{props.item.professor[0].nome}}
                                    </a>
                                </div>
                                <div v-else> ----------------</div>
                            </td>
                            <td class="text-xs-center">{{props.item.alunos.length}}</td>
                            <td class="text-xs-center">{{props.item.ciclo}}</td>
                            <td class="float-md-right"
                                v-if="props.item.professor[0] && $store.state.user.email === props.item.professor[0].email">
                                <v-btn round v-if="props.item.alunos[0]" color="primary"
                                       @click="showTurmaAlunos(props.item)">
                                    <v-icon medium>list</v-icon>
                                    &nbsp Alunos
                                </v-btn>
                                <v-btn round v-if="props.item.alunos[0]" color="deep-orange darken-1"
                                       class="white--text" @click="enviarNotificacao(props.item)">
                                    Notificar &nbsp
                                    <v-icon small>send</v-icon>
                                </v-btn>
                            </td>
                            <td v-else></td>
                            <td class="text-xs-center">
                                <div
                                    v-if="props.item.professor[0] && $store.state.user.email === props.item.professor[0].email">
                                    <v-btn icon color="warning" @click="showEditTurma(props.item)">
                                        <v-icon small>edit</v-icon>
                                    </v-btn>
                                    <v-btn color="error" icon
                                           @click.stop="apagarVerificacao(props.item)">
                                        <v-icon small>delete_forever</v-icon>
                                    </v-btn>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    </template>
                    <template v-slot:no-data>
                        <v-alert v-if="!isLoadingTurmas" :value="true" color="error" icon="warning">
                            Não existem turmas :(
                        </v-alert>
                    </template>
                </v-data-table>
            </v-card>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Se eliminar esta turma, todos alunos ficaram sem turma.</v-card-text>
                <v-card-text>Quer continuar?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagarTurma()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <lista-alunos v-on:atualizar="getMyEscola" :titulo="'Turma ' + turmaAtual.nome + ' - Alunos'"
                      :users="turmaAtual.alunos"></lista-alunos>
        <criar-editar-turma ref="addEditTurma" v-bind:escola="myEscola" :turma="turmaAtual"
                            v-on:getEscolas="atualizarDados"></criar-editar-turma>
        <criar-aluno ref="addAluno" :user="userForm" v-on:getUsers="atualizarDados"></criar-aluno>
        <enviar-notificacao ref="enviarNotificacaoModal" :tipo="'turma'"
                            :users="turmaAtual.alunos"></enviar-notificacao>
    </div>
</template>

<script>
    import listaAlunos from './showUsers';
    import criarTurma from './adicionarEditarTurma';
    import criarAluno from '../users/adicionarUser';
    import enviarNotificacao from '../widgets/enviarNotificacao';

    export default {
        components: {
            'lista-alunos': listaAlunos,
            'criar-editar-turma': criarTurma,
            'criar-aluno': criarAluno,
            'enviar-notificacao': enviarNotificacao
        },
        mounted() {
            this.getMyEscola();
        },
        data() {
            return {
                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 10,
                    sortBy: 'nome',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                ciclosSelected: [],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                search: '',
                tipos: ['Todas', 'Minhas'],
                tipoSelected: 'Todas',

                headers: [
                    {text: 'Nome', value: 'nome', align: 'left'},
                    {text: '', align: 'right', sortable: false},
                    {text: 'Professor', align: 'center', value: 'professor'},
                    {text: 'Numero de Alunos', align: 'center', value: 'numero'},
                    {text: 'Ciclo', align: 'center', value: 'ciclo'},
                    {text: '', align: 'right', value: '', sortable: false},
                    {text: 'Actions', align: 'center', value: 'acoes', sortable: false}
                ],
                myEscola: {
                    nome: '',
                    turmas: [],
                },
                dialog: false,
                turmaAtual: {},
                userForm: {
                    nome: '',
                    email: '',
                    tipo: 'aluno',
                    escola: this.$store.state.user.escola[0],
                    turma: '',
                },
                isLoadingTurmas: true,
            }
        },
        methods: {
            getMyEscola(url = '/api/me/escola') {

                this.isLoadingTurmas = true;
                axios.get(url)
                    .then(response => {
                        this.myEscola = response.data.data;
                        this.isLoadingTurmas = false;
                    }).catch(error => {
                        if (error.response.status == 404){
                            this.toastPopUp("error", 'Não existem turmas na sua escola');
                        } else{
                            this.toastPopUp("error", `${error.response.data.message}`);
                        }
                        this.isLoadingTurmas = false;
                });
            },
            showTurmaAlunos(turma) {
                this.turmaAtual = turma;
                $('#turmaAlunosModal').modal('show');
            },
            showCriarTurma() {
                this.turmaAtual = {};
                this.turmaAtual.professor = this.$store.state.user;
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
            apagarVerificacao(item) {
                this.dialog = true;
                this.turmaAtual = item;
            },
            apagarTurma() {
                this.dialog = false;
                axios.delete('/api/escolas/turmas/' + this.turmaAtual.id)
                    .then(response => {
                        this.toastPopUp("success", "Turma Apagado!");
                        this.atualizarDados();
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                });
                this.turmaAtual = {};
            },
            atualizarDados() {
                this.getMyEscola();
                this.$refs.addAluno.getEscolas();
            },
            enviarNotificacao(turma) {
                this.turmaAtual = turma;
                $('#enviarNotificacaoModal').modal('show');
            },
        },
        computed: {
            filteredTurmas() {
                return this.myEscola.turmas.filter((i) => {
                    return (this.ciclosSelected.length === 0 || this.ciclosSelected.indexOf(i.ciclo) !== -1)
                        && (this.search === "" || i.nome.includes(this.search))
                        && (this.tipoSelected === "Todas" || i.professor.length === 0 ||
                            i.professor[0].email === this.$store.state.user.email);
                });
            }
        }
    }
</script>
<style>
    .zoom:hover {
        -ms-transform: scale(4);
        -webkit-transform: scale(4);
        transform: scale(4);
    }
</style>

<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Turmas / Gerir</h3>
            <br>
            <v-card append float>
                <v-btn color="success" data-toggle="modal" data-target="#addUserModal">Criar Aluno <i
                    class="material-icons">add_box</i>
                </v-btn>
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
                        <v-flex xs12 sm3 d-flex>
                            <v-btn color="success" @click="showCriarTurma">Criar Turma <i
                                class="material-icons">add_box</i>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>

                <v-data-table :headers="headers" :items="filteredTurmas" :search="search" class="elevation-1"
                              :pagination.sync="pagination">
                    <template v-slot:items="props">
                        <tr class="alert-primary">
                            <td class="text-xs-left">{{props.item.nome}}</td>
                            <td class="text-xs-center">
                                <div v-if="props.item.professor[0] && props.item.professor[0].foto">
                                    <div class="zoom">
                                        <img width="30px" height="30px" v-bind:src="getUserPhoto(props.item.professor[0].foto)"/>
                                    </div>
                                    {{props.item.professor[0].nome}}
                                </div>
                            </td>
                            <td class="text-xs-center">{{props.item.alunos.length}}</td>
                            <td class="text-xs-center">{{props.item.ciclo}}</td>
                            <td class="float-md-right">
                                <v-btn v-if="props.item.alunos[0]" color="success"
                                       @click="showTurmaAlunos(props.item)">Listar Alunos
                                    <v-icon medium>list</v-icon>
                                </v-btn>
                            </td>
                            <td>
                                <v-btn v-if="$store.state.user.nome == props.item.professor" color="warning"
                                       @click="showEditTurma(props.item)">Editar
                                    <v-icon small>edit</v-icon>

                                </v-btn>
                                <v-btn v-if="$store.state.user.nome == props.item.professor" color="error"
                                       @click.stop="apagarVerificacao(props.item)">
                                    Apagar
                                    <v-icon small>delete_forever</v-icon>
                                </v-btn>
                            </td>
                            <td></td>
                        </tr>
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
        <br><br>
        <lista-alunos :turma="turmaAtual"></lista-alunos>
        <criar-editar-turma ref="addEditTurma" v-bind:escola="myEscola" :turma="turmaAtual" v-on:getEscolas="getMyEscola"></criar-editar-turma>
        <criar-aluno :user="userForm" v-on:getUsers="getMyEscola"></criar-aluno>
    </div>
</template>

<script>
    import listaAlunos from './showTurmaAlunos';
    import criarTurma from './adicionarTurma';
    import criarAluno from '../users/adicionarUser';

    export default {
        components: {
            'lista-alunos': listaAlunos,
            'criar-editar-turma': criarTurma,
            'criar-aluno': criarAluno,
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
                    {text: 'Professor', align: 'center', value: 'professor'},
                    {text: 'Numero de Alunos', align: 'center', value: 'numero'},
                    {text: 'Ciclo', align: 'center', value: 'ciclo'},
                    {text: '', align: '', value: '', sortable: false},
                    {text: '', align: 'center', value: 'acoes', sortable: false}
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
            }
        },
        methods: {
            getMyEscola(url = '/api/me/escola') {
                axios.get(url)
                    .then(response => {
                        this.myEscola = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            showTurmaAlunos(turma) {
                this.turmaAtual = turma;

                $('#turmaAlunosModal').modal('show');
            },
            showCriarTurma() {
                this.turmaAtual = {};
                this.turmaAtual.professor = this.$store.state.user.nome;
                $('#addTurmaModal').modal('show');
            },
            showEditTurma(turma) {
                this.turmaAtual = Object.assign({}, turma);
                this.turmaAtual.professor = this.turmaAtual.professor[0];
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
                        this.getMyEscola();
                        this.$refs.addEditTurma.getAlunos();
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
                this.turmaAtual = {};
            },
        },
        computed: {
            filteredTurmas() {
                return this.myEscola.turmas.filter((i) => {
                    return (this.ciclosSelected.length === 0 || this.ciclosSelected.indexOf(i.ciclo) !== -1)
                        && (this.search === "" || i.nome.includes(this.search))
                        && (this.tipoSelected === "Todas" || i.professor.includes(this.$store.state.user.nome));
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

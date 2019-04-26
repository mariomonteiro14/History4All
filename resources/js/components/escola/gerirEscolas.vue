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
                            label="Search"
                            single-line
                            hide-details
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
                        <v-btn color="success" data-toggle="modal" data-target="#addEscolaModal">Criar Escola <i
                            class="material-icons">add_box</i>
                        </v-btn>
                    </v-layout>
                </v-container>

                <v-data-table :headers="headersEscola" :items="filteredEscolas" :search="search" class="elevation-1"
                              :pagination.sync="pagination" :expand="expand">
                    <template v-slot:items="props">
                        <tr :class="[props.item.id == escolaAtual.id ? 'alert-success' : '' ]">
                            <td class="text-xs-left">{{ props.item.nome }}</td>
                            <td class="text-xs-left">{{ props.item.distrito }}</td>
                            <td class="text-xs-center">
                                <v-btn v-if="props.item.turmas[0]" color="success" @click="showTurmas(props, props.item)">Listar Turmas
                                    <v-icon medium>list</v-icon>
                                </v-btn>
                            </td>
                            <td class="justify-center layout px-0">
                                <v-btn color="success" @click="showCriarTurma(props.item)">Criar Turma <i
                                    class="material-icons">add_box</i>
                                </v-btn>
                                <v-btn color="error" @click.stop="apagarVerificacao(props.item, true)">
                                    Apagar
                                    <v-icon small>delete_forever</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                    <template v-slot:expand="props" class="alert-success">
                        <v-data-table :headers="headersTurma" :items="escolaAtual.turmas" class="alert-success elevation-1"
                                      hide-actions dark>
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
                                        <v-btn v-if="props.item.alunos[0]" color="success" @click="showTurmaAlunos(props.item)">Listar Alunos
                                            <v-icon medium>list</v-icon>
                                        </v-btn>
                                        <v-btn color="warning" @click="showEditTurma(props.item)">Editar
                                            <v-icon small>edit</v-icon>

                                        </v-btn>
                                        <v-btn color="error" @click.stop="apagarVerificacao(props.item, false)">
                                            Apagar
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
                <v-card-text v-if="!turmaAtual.id">Se eliminar esta escola, todas as turmas, professores e alunos seram eliminados.</v-card-text>
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
        <lista-alunos :turma="turmaAtual"></lista-alunos>
        <criar-escola v-on:getEscolas="getEscolas"></criar-escola>
        <criar-editar-turma ref="addEditTurma" v-bind:escola="escolaAtual" :turma="turmaAtual" v-on:getEscolas="getEscolas"></criar-editar-turma>

    </div>
</template>

<script>
    import listaAlunos from './showTurmaAlunos';
    import criarEscola from './adicionarEscola';
    import criarTurma from './adicionarTurma';

    export default {
        components: {
            'lista-alunos': listaAlunos,
            'criar-escola': criarEscola,
            'criar-editar-turma': criarTurma,
        },
        mounted() {
            this.getEscolas();
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
                    {text: '', align:'center', value: 'turmas', sortable: false},
                    {text: 'Ações', align:'center', value: 'acoes', sortable: false},
                ],
                headersTurma: [
                    {text: 'Nome', value: 'nome', align:'left'},
                    {text: 'Professor', align:'center', value: 'professor'},
                    {text: 'Numero de Alunos', align:'center', value: 'numero'},
                    {text: 'Ciclo', align:'center', value: 'ciclo'},
                    {text: '', align:'', value: 'acoes', sortable: false},
                    {text: '', align:'center', value: '', sortable: false}
                ],
                escolas: [],
                dialog: false,
                escolaAtual: {},
                turmaAtual: {}
            }
        },
        methods: {
            getEscolas(url = '/api/escolas') {
                axios.get(url)
                    .then(response => {
                        this.escolas = response.data.data;
                        if (this.escolaAtual.id){
                            this.escolaAtual = this.escolas.find(e => e.id === this.escolaAtual.id);
                        }
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },

            showTurmas(props ,escola) {
                props.expanded = !props.expanded;
                if(!this.escolaAtual.id || this.escolaAtual.id != escola.id) {
                    this.escolaAtual = escola;
                }else{
                    this.escolaAtual = {};
                }
            },
            showTurmaAlunos(turma){
                this.turmaAtual = turma;

                $('#turmaAlunosModal').modal('show');
            },
            showCriarTurma(escola){
                this.escolaAtual = escola;
                this.turmaAtual = {};
                $('#addTurmaModal').modal('show');
            },
            showEditTurma(turma){
                this.turmaAtual = Object.assign({}, turma);
                this.turmaAtual.professor = this.turmaAtual.professor[0];
                if(turma.alunos) {
                    this.turmaAtual.alunos = turma.alunos.map(a => ({...a}));
                }
                $('#addTurmaModal').modal('show');
            },

            apagarVerificacao(item, escola_boolean) {
                this.dialog = true;
                if (escola_boolean == true) {
                    this.escolaAtual = item;
                }else {
                    this.turmaAtual = item;
                }
            },

            apagarEscola() {
                this.dialog = false;
                axios.delete('/api/escolas/' + this.escolaAtual.id)
                    .then(response => {
                        this.toastPopUp("success", "Escola Apagado!");
                        this.getEscolas();
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
                this.escolaAtual={};
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
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
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

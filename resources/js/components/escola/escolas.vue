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
                        <tr @click="showTurmas(props, props.item)" :class="[props.item.id == escolaAtual.id ? 'alert-success' : '' ]">
                            <td class="text-xs-left">{{ props.item.nome }}</td>
                            <td class="text-xs-left">{{ props.item.distrito }}</td>
                            <td class="justify-center layout px-0">
                                <v-btn color="success" data-toggle="modal" data-target="#addTurmaModal">Criar Turma <i
                                    class="material-icons">add_box</i>
                                </v-btn>
                                <v-btn color="error" @click.stop="apagarVerificacao(props.item.id)">
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
                                    <td class="text-xs-center">{{props.item.professor}}</td>
                                    <td class="text-xs-center">{{props.item.alunos.length}}</td>
                                    <td class="justify-center layout px-0">
                                        <v-btn v-if="props.item.alunos[0]" color="success" data-toggle="modal" data-target="#showStudentsModal">Listar Alunos
                                            <v-icon medium>list</v-icon>
                                        </v-btn>
                                        <v-btn color="warning" data-toggle="modal" data-target="#editTurmaModal">Editar
                                            <v-icon small>edit</v-icon>

                                        </v-btn>
                                        <v-btn color="error" @click.stop="apagarVerificacao(props.item.id)">
                                            Apagar
                                            <v-icon small>delete_forever</v-icon>
                                        </v-btn>
                                    </td>
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
                <v-card-text>Se eliminar esta escola, todas as turmas, professores e alunos seram eliminados. Quer continuar?</v-card-text>
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
    </div>
</template>

<script>
    export default {
        components: {},
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
                    {text: 'Ações', align:'center', value: 'acoes', sortable: false}
                ],

                headersTurma: [
                    {text: 'Nome', value: 'nome', align:'left'},
                    {text: 'Professor', align:'center', value: 'professor'},
                    {text: 'Mumero de Alunos', align:'center', value: 'numero'},
                    {text: 'Ações', align:'center', value: 'acoes', sortable: false}
                ],
                escolas: [],
                dialog: false,
                escolaAtual: {},
            }
        },
        methods: {
            getEscolas(url = 'api/escolas') {
                axios.get(url)
                    .then(response => {
                        this.escolas = response.data.data;
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

            apagarVerificacao(id) {
                this.dialog = true;
                this.escolaAApagar = id;
            },

            apagar(escola) {
                this.dialog = false;
                axios.delete('api/escolas/' + escola.id)
                    .then(response => {
                        this.toastPopUp("success", "Escola Apagado!");
                        this.getPatrimonios();
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
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

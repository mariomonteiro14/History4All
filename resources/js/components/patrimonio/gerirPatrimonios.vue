<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <patrimonio-add-edit :patrimonio="patrimonioAtual" v-on:getPat="getPatrimonios()"></patrimonio-add-edit>
            <h3><a href="/patrimonios">Patrimónios </a>/ Gerir</h3>
            <br>
            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout row align-center>
                        <v-flex xs12 sm4 d-flex>
                            <v-text-field
                                v-model="search"
                                append-icon="pesquisa"
                                label="Pesquisa"
                                single-line
                                hide-details
                                clearable
                            />
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-flex xs12 sm2 d-flex>
                            <v-btn round color="success" data-toggle="modal" data-target="#addPatrimonioModal"
                                   @click="resetPatrimonioAtual()">
                                <v-icon medium>add</v-icon>
                                &nbsp Patrimonio
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>
                </v-container>

                <v-data-table :headers="headers" :items="patrimonios" :search="search" class="elevation-1"
                              :pagination.sync="pagination" :loading="isLoading">
                    <template v-slot:items="props">
                        <tr>
                            <td class="text-xs-center">
                                <img height="70" width="90" v-if="props.item.imagens[0]"
                                     v-bind:src="getPatrimonioPhoto(props.item.imagens[0])"/>
                            </td>
                            <td class="text-xs-left">{{ props.item.nome }}</td>
                            <td class="text-xs-left">{{ props.item.distrito }}</td>
                            <td class="text-xs-left">{{ props.item.epoca }}</td>
                            <td class="text-xs-left">{{ props.item.ciclo }}</td>
                            <td class="justify-left layout px-0">
                                <v-btn flat icon @click="showEdit(props.item)">
                                    <v-icon color="warning" medium>edit</v-icon>
                                </v-btn>
                                <v-btn flat icon  @click.stop="apagarVerificacao(props.item.id)">
                                    <v-icon color="error" medium>delete_forever</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-card>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Tem a certeza que que elimiar o património?</v-card-text>
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
    import AddEditPatrimonio from './adicionarEditarPatrimonio.vue'

    export default {
        components: {
            'patrimonio-add-edit': AddEditPatrimonio
        },
        mounted() {
            this.getPatrimonios();
        },
        data() {
            return {
                patrimonioAtual: {
                    id: undefined,
                    nome: "",
                    descricao: "",
                    distrito: "",
                    epoca: "",
                    ciclo: ""
                },

                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 10,
                    sortBy: 'nome',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                distritoSelected: 'Todos',
                epocaSelected: 'Todas',
                cicloSelected: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                distritos: ['Todos', 'Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                epocas: ['Todas', 'pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                search: '',
                headers: [
                    {
                        text: '',
                        align: 'left',
                        sortable: false,
                        value: 'image'
                    },
                    {text: 'Nome', value: 'nome'},
                    {text: 'Distrito', value: 'distrito'},
                    {text: 'Epoca', value: 'epoca'},
                    {text: 'Ciclo', value: 'ciclo'},
                    {text: 'Ações', value: 'acoes', sortable: false}
                ],
                patrimonios: [],
                dialog: false,
                patrimonioAApagar: '',
                isLoading: true,
            }
        },
        methods: {
            showEdit($pat) {
                this.patrimonioAtual = Object.assign({}, $pat);
                $('#addPatrimonioModal').modal('show');

            },
            getPatrimonios(url = '/api/patrimonios') {
                this.isLoading = true;
                axios.get(url).then(response => {
                    this.patrimonios = response.data.data;
                    this.isLoading = false;
                }).catch(error => {
                    this.isLoading = false;
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },

            apagarVerificacao(id) {
                this.dialog = true;
                this.patrimonioAApagar = id;
            },
            apagar() {
                this.dialog = false;
                axios.delete('/api/patrimonios/' + this.patrimonioAApagar)
                    .then(response => {
                        this.toastPopUp("success", "Património Apagado!");
                        this.getPatrimonios();
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            resetPatrimonioAtual() {
                this.patrimonioAtual.id = undefined;
                this.patrimonioAtual.nome = "";
                this.patrimonioAtual.descricao = "";
                this.patrimonioAtual.distrito = "";
                this.patrimonioAtual.epoca = "";
                this.patrimonioAtual.ciclo = "";
            }
        }
    }
</script>

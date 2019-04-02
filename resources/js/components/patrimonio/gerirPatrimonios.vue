<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <patrimonio-add></patrimonio-add>
            <h3>Patrimónios / Gerir</h3>
            <br>
            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout row  align-center>
                        <v-text-field
                            v-model="search"
                            append-icon="search"
                            label="Search"
                            single-line
                            hide-details
                        ></v-text-field>
                        <v-spacer></v-spacer>
                        <v-btn color="success" @click="criar()">Criar património <i class="material-icons">add_box</i></v-btn>

                        <v-btn color="success" data-toggle="modal" data-target="#addPatrimonioModal">Criar património <i class="material-icons">add_box</i>
                        </v-btn>
                    </v-layout>
                </v-container>

                <v-data-table :headers="headers" :items="patrimonios" :search="search" class="elevation-1"
                              :pagination.sync="pagination">
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
                                <v-btn color="warning" @click="editar(props.item)">
                                    Editar
                                    <v-icon small class="mr-2">edit</v-icon>
                                </v-btn>
                                <v-btn color="error" @click.stop="apagarVerificacao(props.item.id)">
                                    Apagar
                                    <v-icon small>delete_forever</v-icon>
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
                <v-card-text>Tem acerteza que que elimiar o património?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagar()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    import AddPatrimonio from './adicionarPatrimonio.vue'

    export default {
        components:{
          'patrimonio-add':AddPatrimonio
        },
        mounted() {
            this.getPatrimonios();
        },
        data() {
            return {
                pagination: {
                    descending: false,
                    page: 1,
                    rowsPerPage: 5,
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
                patrimonioAApagar: ''
            }
        },
        methods: {
            getPatrimonios(url = 'api/patrimonios') {
                axios.get(url)
                    .then(response => {
                        this.patrimonios = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            editar(patrimonio) {
                this.$parent.patrimonio = patrimonio;
                this.$router.push('/admin/patrimonios/editar');
            },
            apagarVerificacao(id){
                this.dialog = true;
                this.patrimonioAApagar = id;
            },
            apagar() {
                this.dialog = false;
                axios.delete('api/patrimonios/' + this.patrimonioAApagar)
                    .then(response => {
                            this.toastPopUp("success", "Património Apagado!");
                        this.getPatrimonios();
                    }).catch(function (error) {
                        this.toastPopUp("error", "Erro");
                        console.log(error);
                    });
            },
            criar() {
                this.$router.push('/admin/patrimonios/criar');
            }
        }
    }
</script>

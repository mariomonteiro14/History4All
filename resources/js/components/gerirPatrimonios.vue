<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Patrimónios / Gerir</h3>
            <v-btn color="success" @click="criar()">Criar património <i class="material-icons">add_box</i></v-btn>
            <br>
            <v-card append float>
                <v-text-field
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                ></v-text-field>
            </v-card>

            <v-data-table :headers="headers" :items="patrimonios" :search="search" class="elevation-1"
                          :pagination.sync="pagination">
                <template v-slot:items="props">
                    <tr>
                        <td class="text-xs-center">
                            <img  height="70" width="90" v-if="props.item.primeiraImagem"
                                 v-bind:src="getPatrimonioPhoto(props.item.primeiraImagem)"/>
                        </td>
                        <td class="text-xs-left">{{ props.item.nome }}</td>
                        <td class="text-xs-left">{{ props.item.distrito }}</td>
                        <td class="text-xs-left">{{ props.item.epoca }}</td>
                        <td class="text-xs-left">{{ props.item.ciclo }}</td>
                        <td class="justify-center layout px-0">
                            <v-btn color="warning" @click="editar(props.item.id)">
                                Editar <v-icon small class="mr-2">edit</v-icon>
                            </v-btn>
                            <v-btn color="error" @click="apagar(props.item.id)">
                                Apagar <v-icon small>delete_forever</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-app>
    </div>
</template>

<script>

    export default {
        created() {
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
                patrimonios: []
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
            editar(id){

            },
            apagar(id){

            },
            criar(){

            }
        }
    }
</script>

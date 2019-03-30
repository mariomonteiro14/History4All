<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Patrimónios / Pesquisa</h3>
            <br>

            <v-card append float>

                <v-card-title>
                    <v-container fluid grid-list-xl>
                        <v-layout row wrap align-center>
                            <v-flex xs12 sm3 d-flex>
                                <v-select
                                    v-model="distritoSelected"
                                    :items="distritos"
                                    label="Filtrar por distrito"
                                    class="input-group--focused"
                                ></v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-flex xs12 sm3 d-flex>
                                <v-select
                                    v-model="epocaSelected"
                                    :items="epocas"
                                    label="Filtrar por época históricas"
                                    class="input-group--focused"
                                ></v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-flex xs12 sm3>
                                <v-select
                                    v-model="cicloSelected"
                                    :items="ciclos"
                                    chips
                                    label="Filtrar por ciclos"
                                    multiple
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
            <v-data-table :headers="headers" :items="filteredPatrimonios" :search="search" class="elevation-1"
                          :pagination.sync="pagination">

                <template v-slot:items="props">
                    <tr @click="showPatrimonio(props.item)">
                        <td class="text-xs-center">
                            <img  height="70" width="90" v-if="props.item.primeiraImagem"
                                 v-bind:src="getPatrimonioPhoto(props.item.primeiraImagem)"/>
                        </td>
                        <td class="text-xs-left">{{ props.item.nome }}</td>
                        <td class="text-xs-left">{{ props.item.distrito }}</td>
                        <td class="text-xs-left">{{ props.item.epoca }}</td>
                        <td class="text-xs-left">{{ props.item.ciclo }}</td>
                    </tr>
                </template>
            </v-data-table>
        </v-app>
    </div>
</template>

<script>
    import BRow from "bootstrap-vue/src/components/layout/row";

    export default {
        components: {
            BRow,
        },
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
            showPatrimonio(patrimonio) {
                this.$router.push({path: '/patrimonio/' + patrimonio.id, params: {'patrimonio': patrimonio}});
            }
        },
        computed: {
            filteredPatrimonios() {
                return this.patrimonios.filter((i) => {
                    return (this.distritoSelected === 'Todos' || i.distrito === this.distritoSelected)
                        && (this.epocaSelected === 'Todas' || i.epoca === this.epocaSelected)
                        && (this.cicloSelected.indexOf(i.ciclo) > -1);
                });
            }
        }
    }
</script>

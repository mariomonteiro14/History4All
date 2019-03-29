<template>
    <div>
        <br><br><br><br><br>
        <h3>Patrimónios / Pesquisa</h3>
        <br>

        <v-card append float>
            <v-card-title>

                <v-layout align-center wrap>
                    <v-select
                        :items="patrimonios"
                        label="Distrito"
                        @change="filterDistrito"
                    ></v-select>
                    <v-spacer></v-spacer>
                    <v-select
                        v-model="epoca"
                        :items="patrimonios"
                        attach
                        chips
                        label="Epoca"
                        multiple
                    ></v-select>
                    <v-spacer></v-spacer>
                    <v-select
                        v-model="ciclo"
                        :items="patrimonios"
                        attach
                        chips
                        label="Ciclos"
                        multiple
                    ></v-select>
                    <v-spacer></v-spacer>

                    <v-text-field
                        v-model="search"
                        append-icon="search"
                        label="Search"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-layout>
            </v-card-title>
            <v-data-table :headers="headers" :items="patrimonios" :search="search" class="elevation-1"
                          :pagination.sync="pagination">

                <template v-slot:items="props">
                    <td class="text-xs-left">{{ }}</td>
                    <td class="text-xs-left">{{ props.item.nome }}</td>
                    <td class="text-xs-left">{{ props.item.distrito }}</td>
                    <td class="text-xs-left">{{ props.item.epoca }}</td>
                    <td class="text-xs-left">{{ props.item.ciclo }}</td>
                </template>
            </v-data-table>
        </v-card>
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
                    descending: true,
                    page: 1,
                    rowsPerPage: 5,
                    sortBy: 'fat',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                search: '',
                headers: [
                    {
                        text: '',
                        align: 'left',
                        sortable: false,
                        value: 'nome'
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
        }
    }
</script>

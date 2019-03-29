<template>
    <div>
        <br><br><br><br><br>
        <h3>Patrimónios / Pesquisa</h3>
        <br>

        <v-card append float>
            <v-card-title>
                Patrimonios
                <v-spacer></v-spacer>
                <v-text-field
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                ></v-text-field>
            </v-card-title>
            <v-data-table :headers="headers" :items="patrimonios" :search="search">

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

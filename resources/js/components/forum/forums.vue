<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <forum-add-edit :forum="forumAtual" :patrimonios="patrimonios" v-on:geFor="getForums()"></forum-add-edit>
            <h3>Fórum / Pesquisa</h3>
            <br>
            <v-card append float>
                <v-card-title>
                    <v-container fluid grid-list-md>
                        <v-layout row wrap>
                            <v-flex xs12 sm4>
                                <v-text-field
                                    v-model="search"
                                    append-icon="pesquisa"
                                    label="Pesquisar em todos os campos"
                                    single-line
                                    hide-details
                                    clearable
                                    align-bottom
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm4 d-flex>
                                <v-select
                                    v-model="patrimoniosSelected"
                                    :items="patrimonios"
                                    item-text="nome"
                                    chips
                                    label="Filtrar por patrimónios"
                                    multiple
                                    class="input-group--focused custom"
                                    pa-0="" ma-0=""
                                >
                                    <template v-slot:selection="{ item, index }">
                                        <v-chip v-if="index === 0">
                                            <span>{{ item.nome }}</span>
                                        </v-chip>
                                        <span
                                            v-if="index === 1"
                                            class="grey--text caption"
                                        >(+{{ patrimoniosSelected.length - 1 }} outros)</span>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-btn round color="success" data-toggle="modal"
                                data-target="#addForumModal">
                                <v-icon>add</v-icon> &nbsp Fórum
                            </v-btn>
                        </v-layout>
                    </v-container>
                </v-card-title>
            </v-card>       
            <v-data-table :headers="headers" :items="filteredForums" :search="search" class="elevation-1"
                          :pagination.sync="pagination" :loading="isLoading">
                <template v-slot:items="props">
                    <tr @click="showForum(props.item)">
                        <td class="text-xs-left">{{ props.item.titulo }}</td>
                        <td class="text-xs-left">{{ props.item.comentarios.length }}</td>
                    </tr>
                </template>
            </v-data-table>
        </v-app>
        <br><br>
    </div>
</template>

<script>
    import AddEditForum from './adicionarEditarForum.vue'

    export default {
        components: {
            'forum-add-edit': AddEditForum
        },
        created() {
            this.getForums();
            this.getPatrimonios();
        },
        data() {
            return {
                pagination: {
                    descending: true,
                    page: 1,
                    rowsPerPage: 5,
                    sortBy: 'comentarios.length',
                    totalItems: 0,
                    rowsPerPageItems: [5, 10, 20]
                },
                search: '',
                headers: [
                    {text: 'Titulo', value: 'titulo'},
                    {text: 'Número de comentários', value: 'comentarios.length'},
                ],
                forums: [],
                forumAtual: {},
                patrimoniosSelected: [],
                patrimonios: [],
                isLoading: true,
            }
        },
        methods: {
            getForums() {
                this.isLoading= true;
                axios.get('/api/forums')
                    .then(response => {
                        this.forums = response.data.data;
                        this.isLoading= false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading= false;
                    });
            },
            getPatrimonios(){
                this.isLoading= true;
                axios.get('/api/patrimoniosShort')
                    .then(response => {
                        this.patrimonios = response.data.data;
                        this.isLoading= false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading= false;
                    });
            },
            showForum(forum) {
                //this.$router.push({path: '/forum/' + forum.id, params: {'forum': forum}});
            }
        },
        computed: {
            filteredForums() {
                return this.forums.filter((f) => {
                    return this.patrimoniosSelected.length === 0 || this.patrimoniosSelected.length === this.patrimonios.length ||
                        f.patrimonios.some(patrim => this.patrimoniosSelected.includes(patrim.nome));
                });
            }
        }
    }
</script>

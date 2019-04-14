<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Atividades / Pesquisa</h3>
            <br>

            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout row align-center>
                        <v-btn color="info" @click="getAtividades('api/atividades')">Todas</v-btn>
                        <v-btn color="success"
                               @click="getAtividades('api/users/'+$store.state.user.id+'/atividades/pendentes')">
                            Pendentes
                        </v-btn>
                        <v-btn color="warning"
                               @click="getAtividades('api/users/'+$store.state.user.id+'/atividades/concluidas')">
                            Concluídas
                        </v-btn>
                    </v-layout>
                </v-container>
            </v-card>
            <v-card append float>
                <v-card-title>
                    <v-container fluid grid-list-xl>
                        <v-layout row wrap align-center>
                            <v-text-field
                                    v-model="search"
                                    append-icon="search"
                                    label="Pesquisar"
                                    single-line
                                    hide-details
                            ></v-text-field>
                            <v-spacer></v-spacer>
                            <v-flex xs12 sm3 d-flex>
                                <v-select
                                        v-model="epocaSelected"
                                        :items="epocas"
                                        label="Filtrar por época históricas"
                                        class="input-group--focused"
                                ></v-select>
                            </v-flex>
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
                            <v-flex xs12 sm3 d-flex>
                                <v-select
                                        v-model="tipoSelected"
                                        :items="tipos"
                                        label="Filtrar por tipo de atividades"
                                        class="input-group--focused"
                                ></v-select>
                            </v-flex>
                            <v-spacer></v-spacer>
                        </v-layout>
                    </v-container>
                </v-card-title>
            </v-card>
            <v-card>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex v-for="(atividade, index) in filteredAtividades" :key="index">
                            <v-hover>
                                <v-card height="200" width="200" slot-scope="{ hover }" class="mx-auto">
                                    <v-img class="white--text" height="150" width="200" v-if="atividade.imagem"
                                           v-bind:src="getPatrimonioPhoto(atividade.imagem)">
                                        <v-expand-transition>
                                            <div v-if="hover" class="blue darken-4 v-card--reveal white--text"
                                                 style="height: 100%;">
                                                <span>{{atividade.descricao}}</span>
                                            </div>
                                        </v-expand-transition>
                                        <v-container fill-height fluid>
                                            <v-layout fill-height>
                                                <v-flex xs12 align-end flexbox>
                                                    <span>{{atividade.tipo}}</span><br>
                                                    <span>{{atividade.ciclo}}</span><br>
                                                    <span>{{atividade.epoca}}</span><br>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                    <v-card-title>
                                        <div>
                                            <span class="grey--text">{{atividade.coordenador.nome}}</span><br>
                                        </div>
                                    </v-card-title>
                                </v-card>
                            </v-hover>
                        </v-flex>
                    </v-layout>
                </v-container>
                <button v-if="limite < atividadesFiltradasLength" @click="limite += 4">Show more</button>
            </v-card>
        </v-app>
    </div>
</template>

<script>
    export default {
        created() {
            this.getAtividades();
        },
        data() {
            return {
                tipoSelected: 'Todos',
                epocaSelected: 'Todas',
                cicloSelected: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                search: '',

                tipos: ['Todos', 'visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio'],
                epocas: ['Todas', 'pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],

                atividades: [],
                atividadesFiltradasLength: null,
                limite: 4,
            }
        },
        methods: {
            getAtividades(url = 'api/atividades') {
                axios.get(url)
                    .then(response => {
                        this.atividades = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
        },
        computed: {
            filteredAtividades() {
                let atividadesFiltradas = this.atividades.filter((i) => {
                    return (this.tipoSelected === 'Todos' || i.tipo === this.tipoSelected)
                        && (this.epocaSelected === 'Todas' || i.epoca.includes(this.epocaSelected)
                            || i.patrimonios.length === 0)//TODO temporáriamente para aceitar atividades sem patrimonios
                        && (this.cicloSelected.length === 0 || this.cicloSelected.some(ciclo => i.ciclo.includes(ciclo))
                            || i.patrimonios.length === 0)//TODO temporáriamente para aceitar atividades sem patrimonios
                        && (this.search === "" || i.descricao.includes(this.search));
                });
                this.atividadesFiltradasLength = atividadesFiltradas.length;
                return atividadesFiltradas.slice(0, this.limite);
            }
        }
    }
</script>
<style>
    .v-card--reveal {
        align-items: center;
        bottom: 0;
        justify-content: center;
        opacity: .7;
        position: absolute;
        width: 100%;
    }
</style>
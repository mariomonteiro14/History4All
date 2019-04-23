<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Atividades / Pesquisa / {{tipoDePesquisa}}</h3>
            <br>

            <v-card append float>
                <v-container fluid grid-list-xl>
                    <v-layout row align-center>
                        <v-btn color="info" @click="setTipoDePesquisa('Todas')">Todas</v-btn>
                        <v-btn color="success" @click="setTipoDePesquisa('Pendentes')">Pendentes</v-btn>
                        <v-btn color="warning" @click="setTipoDePesquisa('Concluidas')">Concluídas</v-btn>
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
                                    label="Pesquisar pela descrição"
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
                        <v-flex v-for="(atividade, index) in filteredAtividades" :key="index"
                                @click="showAtividade(atividade)">
                            <v-hover>
                                <v-card height="300" width="300" slot-scope="{ hover }" class="mx-auto">
                                    <v-img class="white--text" max-height="250" v-if="atividade.imagem"
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
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.tipo}}</span><br>
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.ciclosFormatados}}</span><br>
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.epocasFormatadas}}</span><br>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                    <v-card-title>
                                        <span class="grey--text" style="position: absolute;">{{atividade.coordenador.nome}}</span><br>
                                    </v-card-title>
                                </v-card>
                            </v-hover>
                        </v-flex>
                    </v-layout>
                </v-container>
                <v-layout align-center justify-center>
                    <v-btn class="text-xs-center" v-if="limite < atividadesFiltradasLength" @click="limite += 4">
                        <i class="material-icons">keyboard_arrow_down</i>
                        Carregar Mais
                        <i class="material-icons">keyboard_arrow_down</i>
                    </v-btn>
                </v-layout>
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
                cicloSelected: [],
                search: '',

                tipos: ['Todos', 'visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio'],
                epocas: ['Todas', 'pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],

                atividades: [],
                atividadesFiltradasLength: null,
                limite: 4,
                tipoDePesquisa: 'Todas'
            }
        },
        methods: {
            setTipoDePesquisa(tipo) {
                this.tipoDePesquisa = tipo;
                let url = '';
                switch (tipo) {
                    case 'Pendentes':
                        url = '/api/users/' + this.$store.state.user.id + '/atividades/pendentes';
                        break;
                    case 'Concluidas':
                        url = '/api/users/' + this.$store.state.user.id + '/atividades/concluidas';
                        break;
                    default:
                        url = '/api/atividades';
                }
                this.getAtividades(url);
                this.limite = 4;
            },
            getAtividades(url = '/api/atividades') {
                axios.get(url)
                    .then(response => {
                        this.atividades = response.data.data;
                        this.ciclosFormatados();
                        this.epocasFormatadas();
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            showAtividade(atividade) {
                this.$router.push({path: '/atividade/' + atividade.id, params: {'atividade': atividade}});
            },
            ciclosFormatados() {
                let todosCiclosFormatados = this.atividades.map(function (atividade) {
                    let ciclos = atividade.ciclo;
                    let str = 'ciclos: ';
                    ciclos.sort();
                    ciclos.forEach(function (ciclo, index) {
                        if (ciclo.includes('ciclo')) {
                            str += ciclo.substring(0, 2);
                        } else {
                            str += ciclo;
                        }
                        if (index !== ciclos.length - 1) {
                            str += ', '
                        }
                    });
                    return str;
                });
                Promise.all(todosCiclosFormatados).then(() => {
                    this.atividades.forEach(function (atividade, index) {
                        atividade.ciclosFormatados = todosCiclosFormatados[index];
                    });
                    this.atividades.splice(this.atividades.length - 1);//atualiza na vista
                });
            },
            epocasFormatadas() {
                let todasEpocasFormatadas = this.atividades.map(function (atividade) {
                    let epocas = atividade.epoca;
                    let str = 'epocas: ';
                    epocas.sort();
                    epocas.forEach(function (epoca, index) {
                        str += epoca;
                        if (index !== epocas.length - 1) {
                            str += ', '
                        }
                    });
                    return str;
                });
                Promise.all(todasEpocasFormatadas).then(() => {
                    this.atividades.forEach(function (atividade, index) {
                        atividade.epocasFormatadas = todasEpocasFormatadas[index];
                    });
                    this.atividades.splice(this.atividades.length - 1);//atualiza na vista
                });
            }
        },
        computed: {
            filteredAtividades() {
                let atividadesFiltradas = this.atividades.filter((i) => {
                    return (this.tipoSelected === 'Todos' || i.tipo === this.tipoSelected)
                        && (this.epocaSelected === 'Todas' || i.epoca.includes(this.epocaSelected))
                        && (this.cicloSelected.length === 0 || this.cicloSelected.length === 4 ||
                            this.cicloSelected.some(ciclo => i.ciclo.includes(ciclo)))
                        && (this.search === "" || i.descricao.includes(this.search));
                });
                this.atividadesFiltradasLength = atividadesFiltradas.length;
                atividadesFiltradas = atividadesFiltradas.slice(0, this.limite);
                return atividadesFiltradas;
            },
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
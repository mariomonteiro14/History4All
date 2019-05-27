<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>{{getTitle}}</h3>
            <br>

            <v-card append float v-if="$store.state.user.tipo !== 'admin'">
                <v-container fluid grid-list-xl>
                    <v-layout wrap align-center>
                        <v-flex xs12 sm3 d-flex>
                            <v-select
                                :items="tiposDePesquisa"
                                v-model="tipoDePesquisaSelected"
                            ></v-select>
                        </v-flex>
                        <v-flex xs3 d-flex
                                v-if="tipoDePesquisaSelected == 'Minhas Atividades' && this.$store.state.user.tipo == 'aluno'">
                            <v-select
                                v-model="minhasAtividadesSelected"
                                :items="minhasAtividades"
                                label="Filtrar pelo progresso"
                                class="input-group--focused"
                            ></v-select>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-btn round v-if="$store.state.user.tipo=='professor'" color="success" data-toggle="modal"
                               data-target="#addAtividadeModal" @click="resetAtividadeAtual()">
                             <v-icon>add</v-icon> &nbsp Atividade
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
                                append-icon="pesquisa"
                                label="Pesquisar por título"
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
                <v-progress-linear v-if="isLoading" v-slot:progress :color="colorDefault"
                                   indeterminate></v-progress-linear>
                <v-container v-else fluid grid-list-md  >
                    <v-alert v-if="atividades.length == 0 && !isLoading" :value="true" color="error" icon="warning">
                        Nao existem atividades :(
                    </v-alert>
                    <v-layout row wrap >
                        <v-flex v-for="(atividade, index) in filteredAtividades" :key="index">
                            <v-hover>
                                <v-card height="300" width="300" slot-scope="{ hover }" class="mx-auto">
                                    <v-img @click="mostrar(atividade.id)" class="white--text" aspect-ratio="1.5"
                                           v-if="atividade.imagem" v-bind:src="getPatrimonioPhoto(atividade.imagem)">
                                        <v-expand-transition>
                                            <div v-if="hover" class="blue darken-4 v-card--reveal white--text"
                                                 style="height: 100%;">
                                                <span>{{atividade.descricao}}</span>
                                            </div>
                                        </v-expand-transition>
                                        <v-container fill-height fluid>
                                            <v-layout fill-height>
                                                <v-flex xs12 align-end flexbox style="text-shadow: 2px 2px #000000">
                                                    <h4>{{atividade.titulo}}</h4>
                                                    {{atividade.tipo}}<br>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                    <v-card-title>
                                        <strong v-if="atividade.coordenador.escola">
                                            {{atividade.coordenador.escola[0]}}
                                        </strong>
                                        <v-spacer></v-spacer>


                                    <div v-if="atividade.coordenador.id == $store.state.user.id">
                                        <v-divider vertical></v-divider>
                                            <v-btn icon color="warning" @click="editar(atividade.id)">
                                                <v-icon small>edit</v-icon>
                                            </v-btn>
                                            <v-btn icon color="error" @click.stop="apagarVerificacao(atividade.id)">
                                                <v-icon small>delete_forever</v-icon>
                                            </v-btn>
                                    </div>
                                    </v-card-title>
                                </v-card>
                            </v-hover>
                        </v-flex>
                    </v-layout>
                </v-container>
                <v-layout align-center justify-center>
                    <v-btn class="text-xs-center" v-if="limite < atividadesFiltradasLength" @click="limite += 8">
                        <i class="material-icons">keyboard_arrow_down</i>
                        Carregar Mais
                        <i class="material-icons">keyboard_arrow_down</i>
                    </v-btn>
                </v-layout>
            </v-card>
        </v-app>
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Tem a certeza que quer elimiar a atividade?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagar()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <atividade-add-edit :atividade="atividadeAtual" v-on:atualizar="atualizar()"
                            v-on:atualizarTipos="getTipos()"></atividade-add-edit>
    </div>
</template>

<script>
    import adicionarEditarAtividade from './adicionarEditarAtividade.vue';

    export default {
        components: {
            'atividade-add-edit': adicionarEditarAtividade,
        },
        created() {
            if (this.$store.state.user.tipo == 'admin') {
                this.tipoDePesquisaSelected = 'Publicas';
            }
            this.getAtividades();
            this.getTipos();
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
                limite: 8,
                tiposDePesquisa: ['Publicas', 'Minhas Atividades'],
                tipoDePesquisaSelected: 'Minhas Atividades',
                minhasAtividades: ['Todas', 'Pendentes', 'Concluídas'],
                minhasAtividadesSelected: 'Todas',

                atividadeAApagar: null,
                dialog: false,
                atividadeAtual: {},
                isLoading: false,
            }
        },
        methods: {
            getAtividades(url = '/api/atividades/publicas') {
                if (this.tipoDePesquisaSelected === 'Minhas Atividades') {
                    url = this.minhasAtividadesSelected === 'Todas' ? '/api/me/atividades/' :
                        this.minhasAtividadesSelected === 'Pendentes' ? '/api/users/' + this.$store.state.user.id + '/atividades/pendentes' :
                            '/api/users/' + this.$store.state.user.id + '/atividades/concluidas';
                }

                this.isLoading = true;
                axios.get(url)
                    .then(response => {
                        this.atividades = response.data.data;
                        this.isLoading = false;
                    }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    this.isLoading = false;
                });
            },
            getTipos() {
                this.isLoading = true;
                axios.get('/api/atividades/tipos')
                    .then(response => {
                        this.tipos = response.data.data;
                        this.tipos.unshift('Todos');
                        this.isLoading = false;
                    }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    this.isLoading = false;
                });
            },
            mostrar(id) {
                this.$router.push('/atividade/' + id);
            },
            atualizar() {
                this.getAtividades();
                $('#addAtividadeModal').modal('show');
            },
            editar(id) {
                axios.get('/api/atividades/' + id)
                    .then(response => {
                        this.atividadeAtual = response.data.data;
                        $('#addAtividadeModal').modal('show');
                    }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });

            },
            apagarVerificacao(id) {
                this.dialog = true;
                this.atividadeAApagar = id;
            },
            apagar() {
                this.dialog = false;
                axios.delete('/api/atividades/' + this.atividadeAApagar)
                    .then(response => {
                        this.toastPopUp("success", "Atividade Apagada!");
                        this.getAtividades();
                    }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            resetAtividadeAtual() {
                this.atividadeAtual = {};
            }
        },
        computed: {
            filteredAtividades() {
                let atividadesFiltradas = this.atividades.filter((i) => {
                    return (this.tipoSelected === 'Todos' || i.tipo === this.tipoSelected)
                        && (this.epocaSelected === 'Todas' || i.epoca.includes(this.epocaSelected))
                        && (this.cicloSelected.length === 0 || this.cicloSelected.length === 4 ||
                            this.cicloSelected.some(ciclo => i.ciclo.includes(ciclo)))
                        && (this.search === "" || i.titulo.includes(this.search));
                });
                this.atividadesFiltradasLength = atividadesFiltradas.length;
                return atividadesFiltradas.slice(0, this.limite);
            },
            getTitle: function () {
                let title = "Atividades / Pesquisa / " + this.tipoDePesquisaSelected;
                if (this.tipoDePesquisaSelected === 'Minhas Atividades' && this.$store.state.user.tipo === 'professor') {
                    title = "Atividades / Gestão";
                }
                if (this.tipoDePesquisaSelected === 'Minhas Atividades' && this.minhasAtividadesSelected !== 'Todas') {
                    title += " / " + this.minhasAtividadesSelected;
                }
                return title;
            },
        },
        watch: {
            tipoDePesquisaSelected() {
                this.getAtividades();
                this.limite = 8;
            },
            minhasAtividadesSelected() {
                this.getAtividades();
                this.limite = 8;
            },
        }
    }
</script>
<style>
    .v-card--reveal {
        align-items: center;
        bottom: 0;
        justify-content: left;
        opacity: .7;
        position: absolute;
        width: 100%;
    }
</style>

<template>
    <div>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>{{getTitle}}</h3>
            <br>

            <v-card append float v-if="this.$store.state.user.tipo !== 'admin'">
                <v-container fluid grid-list-xl>
                    <v-layout wrap align-center>
                        <v-flex xs12 sm3 d-flex>
                            <v-select
                                    :items="tiposDePesquisa"
                                    v-model="tipoDePesquisaSelected"
                            ></v-select>
                        </v-flex>
                        <v-flex xs3 d-flex v-if="tipoDePesquisaSelected === 'Minhas atividades' && this.$store.state.user.tipo === 'aluno'">
                            <v-select
                                    v-model="minhasAtividadesSelected"
                                    :items="minhasAtividades"
                                    label="Filtrar pelo progresso"
                                    class="input-group--focused"
                            ></v-select>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <v-btn v-if="podeGerir" color="success" data-toggle="modal" data-target="#addAtividadeModal" @click="resetAtividadeAtual()">
                            Criar Atividade <i class="material-icons">add_box</i>
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
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex v-for="(atividade, index) in filteredAtividades" :key="index">
                            <v-hover>
                                <v-card @click="mostrar(atividade)" height="300" width="300" slot-scope="{ hover }" class="mx-auto">
                                    <v-img class="white--text" max-height="220" v-if="atividade.patrimonios[0] &&
                                    atividade.patrimonios[0].imagens[0]" v-bind:src="getPatrimonioPhoto(atividade.patrimonios[0].imagens[0])">
                                        <v-expand-transition>
                                            <div v-if="hover" class="blue darken-4 v-card--reveal white--text"
                                                 style="height: 100%;">
                                                <span>{{atividade.descricao}}</span>
                                            </div>
                                        </v-expand-transition>
                                        <v-container fill-height fluid>
                                            <v-layout fill-height>
                                                <v-flex xs12 align-end flexbox>
                                                    <span style="text-shadow: 2px 2px #000000"><h4>{{atividade.titulo}}</h4></span>
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.tipo}}</span><br>
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.ciclosFormatados}}</span><br>
                                                    <span style="text-shadow: 2px 2px #000000">{{atividade.epocasFormatadas}}</span><br>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                    <v-card-title>
                                        <div v-if="podeGerir">
                                            <v-btn color="warning" @click="editar(atividade)">
                                                Editar
                                                <v-icon small class="mr-2">edit</v-icon>
                                            </v-btn>
                                            <v-btn color="error" @click.stop="apagarVerificacao(atividade.id)">
                                                Eliminar
                                                <v-icon small>delete_forever</v-icon>
                                            </v-btn>
                                        </div>
                                        <span v-else class="grey--text" style="position: absolute;">{{atividade.coordenador.nome}}</span>
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
        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text>Tem a certeza que que elimiar a atividade?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="dialog = false">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagar()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <atividade-add-edit :atividade="atividadeAtual" v-on:atualizar="getAtividades()"></atividade-add-edit>
    </div>
</template>

<script>
    import adicionarEditarAtividade from './adicionarEditarAtividade.vue';

    export default {
        components: {
            'atividade-add-edit': adicionarEditarAtividade,
        },
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
                tiposDePesquisa: ['Publicas','Minha Escola', 'Minhas Atividades'],
                tipoDePesquisaSelected: 'Publicas',
                minhasAtividades: ['Todas', 'Pendentes', 'Concluídas'],
                minhasAtividadesSelected: 'Todas',

                atividadeAApagar: null,
                dialog: false,
                atividadeAtual: {
                    id: undefined,
                    titulo: "",
                    descricao: "",
                    tipo: "",
                    numeroElementos: "",
                    visibilidade: "",
                    data: "",
                    participantes: [],
                },
            }
        },
        methods: {
            getAtividades(url = '/api/users/' + this.$store.state.user.id + '/atividades') {
                if (this.tipoDePesquisaSelected === 'Minhas Atividades') {
                    url = '/api/me/atividades/';
                }else if(this.tipoDePesquisaSelected === 'Minha Escola'){
                    url='/api/me/escola/atividades/'
                }
                axios.get(url)
                    .then(response => {
                        this.atividades = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            mostrar(atividade) {
                if (!this.podeGerir) {
                    this.$router.push({path: '/atividade/' + atividade.id, params: {'atividade': atividade}});
                }
            },
            formatarTexto(atividadesRecebidas) {
                atividadesRecebidas.map(atividade => {
                    let ciclos = atividade.ciclo;
                    let cicloString = 'ciclos: ';
                    ciclos.forEach((ciclo, index) => {
                        if (ciclo.includes('ciclo')) {
                            cicloString += ciclo.substring(0, 2);
                        } else {
                            cicloString += ciclo;
                        }
                        if (index !== ciclos.length - 1) {
                            cicloString += ', '
                        }
                    });
                    let epocas = atividade.epoca;
                    let epocaString = 'epocas: ';
                    epocas.forEach((epoca, index) => {
                        epocaString += epoca;
                        if (index !== epocas.length - 1) {
                            epocaString += ', '
                        }
                    });
                    let ativ = this.atividades.filter(a => a.id === atividade.id)[0];
                    ativ.ciclosFormatados = cicloString;
                    ativ.epocasFormatadas = epocaString;
                });
            },
            atualizar() {
                this.getAtividades();
                this.atividadeAtual = {};
                $('#addAtividadeModal').modal('show');
            },
            editar(atividade) {
                this.atividadeAtual = Object.assign({}, atividade);
                $('#addAtividadeModal').modal('show');
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
                    }).catch(function (error) {
                    this.toastPopUp("error", "`${error.response.data.message}`");
                    console.log(error);
                });
            },
            resetAtividadeAtual(){
                this.atividadeAtual.id = null;
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
            getTitle: function() {
                let title = "Atividades / Pesquisa / " + this.tipoDePesquisaSelected;
                if (this.tipoDePesquisaSelected === 'Minhas atividades' && this.$store.state.user.tipo === 'professor'){
                    title = "Atividades / Gestão";
                }
                if (this.tipoDePesquisaSelected === 'Minhas atividades' && this.minhasAtividadesSelected !== 'Todas'){
                    title += " / " + this.minhasAtividadesSelected;
                }
                return title;
            },
            podeGerir: function(){
                return this.tipoDePesquisaSelected === 'Minhas Atividades' && this.$store.state.user.tipo === 'professor';
            }
        },
        watch: {
            filteredAtividades(atividadesFiltradas){
                let atividadesSemTextoFormatado = atividadesFiltradas.filter(a => !a.ciclosFormatados);
                if (atividadesSemTextoFormatado.length !== 0) {
                    this.formatarTexto(atividadesSemTextoFormatado);
                    this.atividades.splice(this.atividades.length);
                }
            },
            tipoDePesquisaSelected(tipo) {
                this.getAtividades();
                this.limite = 4;
            },
            minhasAtividadesSelected(tipo) {
                if (this.tipoDePesquisaSelected === 'Minhas atividades') {
                    let url = '';
                    switch (tipo) {
                        case 'Pendentes':
                            url = '/api/users/' + this.$store.state.user.id + '/atividades/pendentes';
                            break;
                        case 'Concluídas':
                            url = '/api/users/' + this.$store.state.user.id + '/atividades/concluidas';
                            break;
                        default:
                            url = '/api/users/' + this.$store.state.user.id + '/atividades/participadas';
                    }
                    this.getAtividades(url);
                    this.limite = 4;
                }
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

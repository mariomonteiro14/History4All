<template>
    <div id="app">
        <br><br><br><br>
        <v-app id="inspire">

            <v-container fluid grid-list-sm>
                <v-layout justify-center>
                    <v-flex xs12 sm12 md12>
                        <!--ESCOLA -->
                        <my-escola v-bind:escola="escolaEstatisticas" :is-loading="isLoadingEstatisticas"></my-escola>

                        <!-- TURMA -->
                        <br>
                        <div v-if="!$store.state.user.turma">
                            <span>Não tens uma turma atribuida. Contacta o teu professor para resolver a situação</span>
                        </div>
                        <div v-else>
                            <v-progress-linear v-if="!isLoadingEstatisticas && isLoadingTurma" v-slot:progress
                                               :color="colorDefault" indeterminate></v-progress-linear>
                            <v-card v-if="turma.nome">
                                <v-card-title>
                                    <v-flex sm3>
                                        <v-layout>
                                            <h2>Turma {{turma.nome}} </h2>
                                        </v-layout>
                                    </v-flex>
                                    <v-flex sm8>
                                        <v-layout>

                                            <v-flex>
                                                <span class=" font-weight-light grey--text">Ciclo:</span>
                                                <h6>{{turma.ciclo}}</h6>
                                            </v-flex>
                                            <v-flex v-if="turma.professor.length > 0">
                                                <span class=" font-weight-light grey--text">Professor:</span>
                                                <a @click="$router.push('/users/'+ turma.professor[0].id)">
                                                    <v-layout>
                                                        <v-avatar size="32px" class="bg-white"
                                                                  v-if="turma.professor[0].foto">
                                                            <v-img v-bind:src="getUserPhoto(turma.professor[0].foto)">
                                                                <template v-slot:placeholder>
                                                                    <v-layout
                                                                        fill-height
                                                                        align-center
                                                                        justify-center
                                                                        ma-0
                                                                    >
                                                                        <v-progress-circular indeterminate
                                                                                             color="grey lighten-5"></v-progress-circular>
                                                                    </v-layout>
                                                                </template>
                                                            </v-img>
                                                        </v-avatar>
                                                        <h6>{{turma.professor[0].nome}}</h6>
                                                    </v-layout>
                                                </a>
                                            </v-flex>

                                            <v-flex v-if="turma.alunos">
                                                <span class=" font-weight-light grey--text">Numero de alunos:</span>
                                                <h6>{{turma.alunos.length}}</h6>
                                            </v-flex>

                                            <v-flex>
                                                <v-btn flat round @click="showAlunos = !showAlunos">Alunos
                                                    <v-icon>{{ !showAlunos ? 'keyboard_arrow_down' : 'keyboard_arrow_up'
                                                        }}
                                                    </v-icon>
                                                </v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                </v-card-title>
                                <v-slide-y-transition>
                                    <v-card-text v-show="showAlunos">
                                        <v-divider></v-divider>
                                        <v-layout align-start justify-start style="overflow-x:auto">
                                            <div  v-for="aluno in turma.alunos" :key="aluno.id"
                                                    @click="$router.push('/users/'+ aluno.id)"
                                                    class="col-lg-1 align-content-center">
                                                <a>
                                                    <v-avatar size="55px" class="bg-white">
                                                        <v-img v-if="aluno.foto" v-bind:src="getUserPhoto(aluno.foto)">
                                                            <template v-slot:placeholder>
                                                                <v-layout
                                                                    fill-height
                                                                    align-center
                                                                    justify-center
                                                                    ma-0
                                                                >
                                                                    <v-progress-circular indeterminate
                                                                                         color="grey lighten-5"></v-progress-circular>
                                                                </v-layout>
                                                            </template>
                                                        </v-img>
                                                        <v-icon v-else class="indigo--text" small>far fa-user</v-icon>
                                                    </v-avatar>
                                                    <br>
                                                    <h5 class="align-center" v-if="aluno.id != $store.state.user.id">{{getPrimeiroUltimoNome(aluno.nome)}}</h5>
                                                    <h5 class="align-center" v-else>Eu</h5>
                                                </a>
                                            </div>
                                        </v-layout>
                                    </v-card-text>
                                </v-slide-y-transition>
                            </v-card>
                        </div>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-app>
    </div>

</template>
<script type="text/javascript">
    import BRow from "bootstrap-vue/src/components/layout/row";
    import myEscola from '../escola/myEscola.vue';

    export default {
        components: {
            BRow,
            'my-escola': myEscola
        },
        data: function () {
            return {
                escolaEstatisticas: {},
                isLoadingEstatisticas: false,
                isLoadingTurma: false,
                turma: {},
                showAlunos: false,
            };
        },

        mounted() {
            this.getMyEscolaEstatisticas();
            if (this.$store.state.user.turma) {
                this.getMyTurma();
            }
        },

        methods: {
            getMyEscolaEstatisticas(url = '/api/me/escola/estatisticas') {
                this.isLoadingEstatisticas = true;

                axios.get(url).then(response => {
                    this.escolaEstatisticas = response.data.data;
                    this.isLoadingEstatisticas = false;
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    this.isLoadingEstatisticas = false;
                });
            },
            getMyTurma(url = '/api/me/escola/turma') {
                this.isLoadingTurma = true;

                axios.get(url).then(response => {
                    this.turma = response.data.data;
                    this.isLoadingTurma = false;
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    this.isLoadingTurma = false;
                });
            }
        }
    }
</script>


<template>
    <div id="app">
        <br><br><br><br>
        <v-app id="inspire">

            <v-tabs v-model="tabSelecionada" fixed-tabs>
                <v-tab>Escola</v-tab>
                <v-tab>Turmas / Alunos</v-tab>
                <v-tab>Professores / Chat</v-tab>
            </v-tabs>

            <v-container fluid grid-list-sm>
                <v-layout justify-center>
                    <v-flex xs12 sm12 md12>
                        <!--ESCOLA -->
                        <my-escola :escola="escolaEstatisticas" :is-loading="isLoadingEstatisticas"
                                   v-if="tabSelecionada == 0"></my-escola>

                        <!-- TURMAS -->
                        <turmas :is-loading-turmas="isLoadingTurmas" :myEscola="myEscola"
                                v-if="tabSelecionada == 1"></turmas>

                        <!--PROFESSORES CHAT-->
                        <professores-chat :professores="myEscola.professores" v-if="tabSelecionada == 2"></professores-chat>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-app>
    </div>

</template>
<script type="text/javascript">
    import BRow from "bootstrap-vue/src/components/layout/row";
    import turmas from '../escola/turmas.vue';
    import professoresChat from '../widgets/chatProfessores.vue';
    import myEscola from '../escola/myEscola.vue';

    export default {
        props: ['tab'],
        components: {
            BRow,
            'turmas': turmas,
            'professores-chat': professoresChat,
            'my-escola': myEscola
        },

        data: function () {
            return {
                tabSelecionada: 0,
                myEscola: {
                    nome: '',
                    turmas: [],
                },
                escolaEstatisticas: {},
                isLoadingTurmas: false,
                isLoadingEstatisticas: false,
            };
        },

        mounted() {
            if (this.tab && this.tab > 0 && this.tab <3 ) {
                this.tabSelecionada = this.tab;
            }
            this.getMyEscola();
            this.getMyEscolaEstatisticas();
        },

        methods: {
            getMyEscola(url = '/api/me/escola') {
                this.isLoadingTurmas = true;
                axios.get(url)
                    .then(response => {
                        this.myEscola = response.data.data;
                        this.isLoadingTurmas = false;
                    }).catch(error => {
                    if (error.response.status == 404) {
                        this.toastPopUp("error", 'Não existem turmas na sua escola');
                    } else {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    }
                    this.isLoadingTurmas = false;
                });
            },

            getMyEscolaEstatisticas(url = '/api/me/escola/estatisticas') {
                this.isLoadingEstatisticas = true;

                axios.get(url).then(response => {
                    this.escolaEstatisticas = response.data.data;
                    this.isLoadingEstatisticas = false;
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    this.isLoadingEstatisticas = false;
                });

            }
        }
    }
</script>


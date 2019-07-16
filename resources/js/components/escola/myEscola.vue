<template>
    <div>
        <div v-if="isLoading">
            <br><br><br>
            <v-progress-linear v-slot:progress :color="colorDefault"
                               indeterminate></v-progress-linear>
            <br><br><br><br><br><br><br><br><br>
        </div>
        <v-card v-else>
            <v-card-title primary-title>
                <div>
                    <h2 class="headline mb-0">{{escola.nome}}</h2>
                    <h4 class="grey--text">{{escola.distrito}}</h4>
                </div>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text primary-title>
                <v-layout>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de professores:</span>
                        <h6>{{escola.numeroProfessores}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de alunos:</span>
                        <h6>{{escola.numeroAlunos}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de turmas:</span>
                        <h6>{{escola.numeroTurmas}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Media de alunos por turma:</span>
                        <h6>{{mediaAlunosTurma}}</h6>
                    </v-flex>
                </v-layout>
                <br>
                <v-layout>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de atividades:</span>
                        <h6>{{escola.numeroAtividades}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de atividades concluidas:</span>
                        <h6>{{escola.numeroAtividadesConcluidas}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de atividades a decorrer:</span>
                        <h6>{{escola.numeroAtividadesADecorrer}}</h6>
                    </v-flex>
                    <v-flex>
                        <span class=" font-weight-light grey--text">Numero de atividades agendadas:</span>
                        <h6>{{escola.numeroAtividadesAgendadas}}</h6>
                    </v-flex>
                </v-layout>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>

    export default {
        props: ['escola', 'isLoading'],

        computed: {
            mediaAlunosTurma() {
                if (this.escola.numeroTurmas && this.escola.numeroTurmas > 0) {
                    return parseInt(this.escola.numeroAlunos / this.escola.numeroTurmas);
                }
                return 0;
            }

        }

    }
</script>

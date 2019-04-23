<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="turmaAlunosModal" tabindex="-1" role="dialog" aria-labelledby="turmaAlunosModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="turmaAlunosModal">Turma {{turma.nome}} - Alunos</h5>
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-list class="form-group" subheader>
                                <v-list-tile
                                    v-for="aluno in turma.alunos"
                                    :key="aluno.title"
                                    avatar
                                >
                                    <v-list-tile-avatar>
                                        <img v-if="aluno.foto" :src="getUserPhoto(aluno.foto)">
                                        <span v-else>{{aluno.nome[0]}}</span>
                                    </v-list-tile-avatar>

                                    <v-list-tile-content>
                                        <v-list-tile-title>{{aluno.nome}}</v-list-tile-title>
                                    </v-list-tile-content>
                                </v-list-tile>
                        </v-list>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>

    export default {

        props: ['turma'],


        data: function () {
            return {

            };
        },
        methods: {


            },
    };

</script>

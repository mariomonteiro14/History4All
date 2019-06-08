<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="turmaAlunosModal" tabindex="-1" role="dialog" aria-labelledby="turmaAlunosModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="turmaAlunosModal"> {{titulo}} </h5>
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-list class="form-group" subheader>
                            <v-list-tile
                                v-for="user in users"
                                :key="user.title"
                                avatar
                            >
                                <v-list-tile-avatar>
                                    <img v-if="user.foto" :src="getUserPhoto(user.foto)">
                                    <v-icon v-else class="indigo--text darken-4" small>far fa-user</v-icon>
                                </v-list-tile-avatar>

                                <v-list-tile-content>
                                    <v-list-tile-title>
                                        <a class="indigo--text darken-4 btn" @click="showAlunoPerfil(user.id)">
                                            {{user.nome}}
                                        </a>

                                    </v-list-tile-title>
                                </v-list-tile-content>
                                <v-list-tile-action>
                                    <v-btn icon flat @click.stop="showDeleteVerif(user)">
                                        <v-icon color="error" medium>delete_forever</v-icon>
                                    </v-btn>
                                </v-list-tile-action>
                            </v-list-tile>
                        </v-list>
                    </div>
                </div>

            </div>
        </div>

        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Confirmação</v-card-title>
                <v-card-text v-if="userAApagar.tipo == 'aluno'">Tem a certeza que quer eliminar o aluno?</v-card-text>
                <v-card-text v-else>
                    Todas as turmas associadas a este professor ficaram sem professor.
                    <p>Tem a certeza que quer continuar?</p>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="closeDialog">
                        Não
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" @click="apagar()">Sim</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>

    export default {

        props: ['titulo', 'users'],


        data: function () {
            return {
                userAApagar: {},
                dialog: false,
            };
        },
        methods: {
            showAlunoPerfil($id) {
                $('#turmaAlunosModal').modal('hide');
                this.$router.push('/users/' + $id);
            },
            showDeleteVerif(user) {
                $('#turmaAlunosModal').modal('hide');
                this.dialog = true;
                this.userAApagar = user;
            },
            apagar() {
                this.dialog = false;
                axios.delete('/api/users/' + this.userAApagar.id)
                    .then(response => {
                        this.toastPopUp("success", "Utilizador Apagado!");
                        this.$emit('atualizar');
                    }).catch(error => {
                        this.toastErrorApi(error);
                        $('#turmaAlunosModal').modal('show');
                });
            },
            closeDialog(){
                this.dialog = false;
                $('#turmaAlunosModal').modal('show');
            }

        },
    };

</script>

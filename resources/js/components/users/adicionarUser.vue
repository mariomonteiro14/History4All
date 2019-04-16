<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModal">Criar Utilizador</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputNome"
                                          v-model="user.nome"
                                          label="Nome"
                                          :rules="[v => !!v || 'Nome é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputEmail"
                                          v-model="user.email"
                                          label="Email"
                                          :rules="emailRules"
                                          clearable
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-select
                                fixed
                                label="Tipo"
                                v-model="user.tipo"
                                :items="userTipos"
                                :rules="[v => !!v || 'Distrito é obrigatório']"
                                class="input-group--focused"
                                required
                            ></v-select>
                        </div>

                        <div class="form-group" v-if="user.tipo && user.tipo != 'admin'">
                            <v-select
                                fixed
                                label="Escola"
                                v-model="user.escola"
                                :items="escolas"
                                item-text="nome"
                                :rules="[v => !!v || 'Escola é obrigatório']"
                                class="input-group--focused"
                                required
                            ></v-select>
                        </div>
                        <div class="form-group" v-if="user.escola && user.tipo == 'aluno'">
                            <v-select
                                fixed
                                label="Turma"
                                v-model="user.turma"
                                :items="turmas"
                                :rules="[v => !!v || 'Turma é obrigatório']"
                                class="input-group--focused"
                                required
                            ></v-select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="save">Registar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        created() {
            this.getEscolas();
        },
        data: function () {
            return {
                user: {
                    nome: '',
                    email: '',
                    tipo: '',
                    escola: {},
                    turma: '',
                },
                escolas: [],
                userTipos: ['admin', 'professor', 'aluno'],
                emailRules: [
                    (v) => !!v || 'E-mail is required',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
                ],
            };
        },
        methods: {

            hasErrors: function () {
                if (!this.user.nome || !this.user.email || !this.user.tipo) {
                    return true;
                }
                if (this.user.tipo === "professor" && !this.user.escola) {
                    return true;
                }
                if (this.user.tipo === "aluno" && (!this.user.escola || !this.user.turma)) {
                    return true;
                }
                return false;
            },
            save: function () {
                if (!this.hasErrors()) {
                    console.log(this.user);
                    axios.post('/api/users', this.user).then(response => {
                        this.toastPopUp("success", "Utilizador Criado!");
                        this.cleanForm();
                        this.$emit('getUsers');
                        $('#addUserModal').modal('hide');
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    })
                }

            },

            cancel: function () {
                this.cleanForm();
                $('#addUserModal').modal('hide');
            },
            cleanForm: function () {
                this.user.nome = "";
                this.user.tipo = "";
                this.user.email = "";
                this.user.escola = {};
                this.user.turma = {};
            },
            getEscolas: function () {
                axios.get("api/escolas").then(response => {
                    this.escolas = response.data.data;
                }).catch(errors => {
                    console.log(errors);
                });
            },
        },
        computed: {
            turmas: function () {
                for (let i in this.escolas) {
                    if (this.escolas[i].nome === this.user.escola) {
                        return this.escolas[i].turmas;
                    }
                }
            }
        }
    }

</script>

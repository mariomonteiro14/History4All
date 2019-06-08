<template>
    <div @focusout="closeLists">
        <!-- Modal Add Order-->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disable="isLoading">
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
                        <div class="form-group" @click="setOpenList" v-if="showType == 'true'">
                            <v-select
                                ref="selectT"
                                label="Tipo"
                                v-model="user.tipo"
                                :items="userTipos"
                                :rules="[v => !!v || 'Tipo é obrigatório']"
                                class="input-group--focused"
                                required
                            ></v-select>
                        </div>

                        <div class="form-group"
                             v-if="user.tipo && user.tipo != 'admin' && $store.state.user.tipo=='admin'"
                             @click="setOpenList">
                            <v-select
                                ref="selectE"
                                label="Escola"
                                v-model="user.escola"
                                :items="escolas"
                                item-text="nome"
                                :rules="[v => !!v || 'Escola é obrigatório']"
                                class="input-group--focused"
                                clearable
                                :disabled="$store.state.user.tipo=='professor'"
                                required
                            ></v-select>
                        </div>
                        <div class="form-group" v-if="user.escola && user.tipo == 'aluno'" @click="setOpenList">
                            <v-select
                                ref="selectT"
                                label="Turma"
                                v-model="user.turma"
                                :items="turmas"
                                item-text="nome"
                                class="input-group--focused"
                                :rules="[(v) => (!(!v && this.$store.state.user.tipo == 'professor')) || 'Turma é obrigatório']"
                                clearable
                            ></v-select>
                        </div>

                    </div>

                    <div v-if="!isLoading" class="modal-footer">
                        <button class="btn btn-info" :disabled="hasErrors" v-on:click.prevent="save">Registar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
                    </div>


                    </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user','showType'],
        created() {
            this.getEscolas();
           // console.log(this.showType);
        },
        data: function () {
            return {
                escolas: [],
                userTipos: ['admin', 'professor'],
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                selAberto: false,
                isLoading: false,
            };
        },
        methods: {

            save: function () {
                console.log(this.user);
                this.isLoading = true;
                axios.post('/api/users', this.user).then(response => {
                    this.toastPopUp("success", "Utilizador Criado!");
                    this.cleanForm();
                    this.$emit('getUsers');
                    $('#addUserModal').modal('hide');
                    this.isLoading = false;

                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                })
            },

            cancel: function () {
                this.cleanForm();
                $('#addUserModal').modal('hide');
            },
            cleanForm: function () {
                this.user.nome = "";
                this.user.email = "";
                if (this.$store.state.user.tipo == "admin") {
                    this.user.escola = "";
                }
                this.user.turma = "";
            },
            getEscolas: function () {
                axios.get("/api/escolas").then(response => {
                    this.escolas = response.data.data;
                }).catch(error => {
                    this.toastErrorApi(error);
                });
            },

            setOpenList() {
                setTimeout(() => {
                    if ((this.$refs.selectT && this.$refs.selectT.isMenuActive == true) || (this.$refs.selectE && this.$refs.selectE.isMenuActive == true) || (this.$refs.selectA && this.$refs.selectA.isMenuActive == true)) {
                        setTimeout(() => {
                            this.selAberto = true;
                        }, 30);
                    }
                }, 10)
            },

            closeLists() {
                if (this.selAberto == true) {
                    this.selAberto = false;
                    if (this.$refs.selectT) {
                        this.$refs.selectT.isMenuActive = false;
                    }
                    if (this.$refs.selectE) {
                        this.$refs.selectE.isMenuActive = false;
                    }
                    if (this.$refs.selectA) {
                        this.$refs.selectA.isMenuActive = false;
                    }
                }

            },

        },
        computed: {
            turmas: function () {
                let turmas = [];
                for (let i in this.escolas) {
                    if (this.escolas[i].nome === this.user.escola) {
                        for (let j in this.escolas[i].turmas) {
                            let turma=this.escolas[i].turmas[j];
                            if (turma.professor[0] && turma.professor[0].id == this.$store.state.user.id) {
                                turmas.push(turma)
                            }
                        }
                        return turmas;
                    }
                }
            },
            hasErrors: function () {
                if (!this.user.nome || !this.user.email || !this.user.tipo) {
                    return true;
                }
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.user.email).toLowerCase())) {
                    return true;
                }
                if (this.user.tipo !== "admin" && this.user.escola == "") {
                    return true;
                }

                //obrigatorio porfessor mensionar turma ao criar aluno
                if (this.user.turma === "" && this.$store.state.user.tipo == "professor") {
                    return true;
                }
                return false;
            },
            getTitle() {
                if (this.user.tipo == 'admin'){
                    return "Adicionar Administrador";
                }
                if (this.user.tipo == 'professor'){
                    return "Adicionar Professor";
                }
                if (this.user.tipo == 'aluno') {
                    return "Adicionar Aluno";
                }
                return "Adicionar Utilizador";


            },
        },
        watch: {
            'user.escola': function (newVal, oldVal) {
                this.user.escola = newVal;
                this.user.turma = undefined;
            }
        },
    }

</script>

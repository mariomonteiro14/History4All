<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addTurmaModal" tabindex="-1" role="dialog" aria-labelledby="addTurmaModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTurmaModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div @click="closeLists" class="size-modal-content">
                            <br>
                            <div class="form-group">
                                <v-text-field id="inputNome"
                                              v-model="turma.nome"
                                              label="Nome"
                                              :rules="[v => !!v || 'Nome é obrigatório']"
                                              required
                                ></v-text-field>
                            </div>

                            <div @click="setOpenList('professor')">
                                <v-select
                                    label="Professor"
                                    v-model="turma.professor"
                                    :items="filteredProfessores"
                                    item-text="nome"
                                    class="input-group--focused"
                                    clearable
                                    :disabled="this.$store.state.user.tipo != 'admin'"
                                    ref="selectProfessor"
                                    @click="selProfAberto=true"
                                ></v-select>
                            </div>

                            <div @click="setOpenList('ciclo')">
                                <v-select
                                    label="Ciclo"
                                    v-model="turma.ciclo"
                                    :items="ciclos"
                                    :rules="[v => !!v || 'Ciclo é obrigatório']"
                                    class="input-group--focused"
                                    clearable
                                    ref="selectCiclo"
                                    required
                                ></v-select>
                            </div>

                            <div @click="setOpenList('alunos')">
                                <v-combobox
                                    v-model="alunosSelected"
                                    :items="filteredAlunos"
                                    item-text="nome"
                                    label="Alunos"
                                    multiple
                                    chips
                                    class="input-group--focused"
                                    deletable-chips
                                    ref="selectAlunos"
                                    autofocus
                                ></v-combobox>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button v-if="!turma.id" class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">
                            Registar
                        </button>
                        <button v-else class="btn btn-info" v-on:click.prevent="edit" :disabled="hasErrors">Guardar
                        </button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['escola', 'turma'],

        created() {
            if (this.$store.state.user.tipo == "professor") {
                this.turma.professor = this.$store.state.user.name;
                this.getAlunos();
                return;
            }
            this.getProfessores();
            this.getAlunos();
        },

        data: function () {
            return {
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                professores: [],
                alunos: [],
                alunosSelected: [],
                selProfAberto: false,
                selCicloAberto: false,
                selAlunosAberto: false,
            };
        },
        methods: {
            save: function () {
                axios.post('/api/escolas/' + this.escola.id + '/turmas', this.turma).then(response => {
                    this.toastPopUp("success", "Turma Criada!");
                    this.cleanForm();
                    this.$emit('getEscolas');
                    $('#addTurmaModal').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            edit: function () {
                axios.put('/api/escolas/turmas/' + this.turma.id, this.turma).then(response => {
                    this.toastPopUp("success", "Turma Atualizada!");
                    this.cleanForm();
                    this.$emit('getEscolas');
                    $('#addTurmaModal').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },

            cancel: function () {
                this.cleanForm();
                $('#addTurmaModal').modal('hide');
            },

            cleanForm: function () {
                this.turma.id = undefined;
                this.turma.nome = "";
                this.turma.ciclo = "";
                this.turma.professor = undefined;
            },
            getProfessores(url = '/api/users/professores') {
                axios.get(url)
                    .then(response => {
                        this.professores = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            getAlunos(url = '/api/users/alunos') {
                axios.get(url)
                    .then(response => {
                        this.alunos = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            setOpenList(lista){
                setTimeout(() =>{
                if (lista == "ciclo" && this.$refs.selectCiclo.isMenuActive == true) {
                    setTimeout(() => {
                        this.selCicloAberto = true;
                    }, 30);
                }

                if (lista == "professor" && this.$refs.selectProfessor.isMenuActive == true) {
                    setTimeout(() => {
                        this.selProfAberto = true;
                    }, 30);
                }

                if (lista == "alunos" && this.$refs.selectAlunos.isMenuActive == true) {
                    setTimeout(() => {
                        this.selAlunosAberto = true;
                    }, 30);
                }
                },10)
            },

            closeLists() {
                if (this.selCicloAberto == true) {
                    this.selCicloAberto = false;
                    this.$refs.selectCiclo.isMenuActive = false;
                    this.turma.ciclo="";
                }
                if (this.selProfAberto == true) {
                    this.$refs.selectProfessor.isMenuActive = false;
                    this.selProfAberto = false
                }
                if (this.selAlunosAberto == true) {
                    this.$refs.selectAlunos.isMenuActive = false;
                    this.selAlunosAberto = false;
                }
            },
        },

        computed: {
            hasErrors: function () {
                if (!this.turma.nome || !this.turma.ciclo) {
                    return true;
                }
                return false;
            },
            filteredProfessores() {
                if (this.$store.state.user.tipo == "admin") {
                    return this.professores.filter((i) => {
                        return i.escola[0] === this.escola.nome;
                    });
                }
                return [{nome: this.turma.professor}];
            },

            filteredAlunos() {
                return this.alunos.filter((i) => {
                    return (i.escola[0] === this.escola.nome) && !i.turma[0];
                });

            },
            getTitle() {
                return !this.turma.id ? this.escola.nome + " - Criar Turma" : "Editar Turma " + this.turma.nome;
            },

        }
    }
</script>

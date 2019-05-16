<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addTurmaModal" tabindex="-1" role="dialog" aria-labelledby="addTurmaModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTurmaModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
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
                                item-value="email"
                                class="input-group--focused"
                                clearable
                                :disabled="this.$store.state.user.tipo != 'admin'"
                                ref="selectProfessor"
                                @click="selProfAberto=true"
                            >
                                <template slot="item" slot-scope="data" v-if="data.item">

                                    <v-list-tile-avatar>
                                        <img v-if="data.item.foto && data.item.foto!=''" width="30px" height="30px"
                                             v-bind:src="getUserPhoto(data.item.foto)"/>
                                        <span v-else>{{data.item.nome[0]}}</span>
                                    </v-list-tile-avatar>
                                    <v-list-tile-title v-html="data.item.nome"></v-list-tile-title>

                                </template>
                            </v-select>
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
                                v-model="turma.alunos"
                                :items="filteredAlunos"
                                item-text="nome"
                                label="Alunos"
                                multiple
                                chips
                                class="input-group--focused"
                                deletable-chips
                                ref="selectAlunos"
                                autofocus
                                :disabled="filteredAlunos.length == 0"
                            >
                                <!--<template v-slot:selection="data">
                                    <v-chip
                                        :selected="data.selected"
                                        close
                                        @input="removeAluno(data.item)"
                                    >
                                        <strong>{{ data.item.nome }}</strong>&nbsp;
                                    </v-chip>
                                </template>-->
                            </v-combobox>
                            <span v-if="filteredAlunos.length == 0">Não existem alunos disponiveis. Pode criar a turma sem alunos e adiciona-los mais tarde.</span>
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
            this.getAlunos();
            if (this.$store.state.user.tipo != "professor") {
                this.getProfessores();
            }
        },
        data: function () {
            return {
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                professores: [],
                alunos: [],
                alunosSelected: [],
                removedAlunos: false,

                selProfAberto: false,
                selCicloAberto: false,
                selAlunosAberto: false,
            };
        },
        methods: {
            removeAluno(aluno) {
                this.alunosSelected.splice(this.alunosSelected.indexOf(aluno), 1);
                this.alunosSelected.alunos = [...this.alunosSelected];
            },
            save: function () {
                if (this.turma.professor && this.turma.professor.email) {
                    this.turma.professor = this.turma.professor.email;
                }

                axios.post('/api/escolas/' + this.escola.id + '/turmas', this.turma).then(response => {
                    this.toastPopUp("success", "Turma Criada!");
                    this.cleanForm();
                    this.getAlunos();
                    this.$emit('getEscolas');
                    $('#addTurmaModal').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            edit: function () {
                if (this.turma.professor && this.turma.professor.email) {
                    this.turma.professor = this.turma.professor.email;
                }

                axios.put('/api/escolas/turmas/' + this.turma.id, this.turma).then(response => {
                    this.toastPopUp("success", "Turma Atualizada!");
                    this.cleanForm();
                    this.getAlunos();
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
                this.closeLists();
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
                if (this.$store.state.user.tipo == 'professor'){
                    url = "/api/me/escola/alunos"
                }
                axios.get(url)
                    .then(response => {
                        this.alunos = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            ////////////////////////////////////////////////////////
            //Metodos pra correçao de bug relacionado com os selects
            setOpenList(lista) {
                setTimeout(() => {
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
                }, 10)
            },

            closeLists() {
                if (this.selCicloAberto == true) {
                    this.selCicloAberto = false;
                    this.$refs.selectCiclo.isMenuActive = false;
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
            ////////////////////////////////////////////////////////
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
                return [this.$store.state.user];
            },

            filteredAlunos() {
                return this.alunos.filter((i) => {
                    return (i.escola[0] === this.escola.nome) && (!i.turma[0] || i.turma[0] === this.turma.nome);
                });

            },
            getTitle() {
                return !this.turma.id ? this.escola.nome + " - Adicionar Turma" : "Editar Turma " + this.turma.nome;
            },
        },

    }
</script>

<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addTurmaModal" tabindex="-1" role="dialog" aria-labelledby="addTurmaModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTurmaModal">{{escola.nome}} - Criar Turma</h5>
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

                        <v-select
                            label="Professor"
                            v-model="turma.professor"
                            :items="filteredProfessores"
                            item-text="nome"
                            class="input-group--focused"
                        ></v-select>

                        <v-select
                            label="Ciclo"
                            v-model="turma.ciclo"
                            :items="ciclos"
                            :rules="[v => !!v || 'Ciclo é obrigatório']"
                            class="input-group--focused"
                            required
                        ></v-select>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">Registar
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
        props: ['escola'],

        created() {
            this.getProfessores();
        },

        data: function () {
            return {
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                turma: {
                    nome: "",
                    ciclo: ""
                },
                professores: [],
            };
        },
        methods: {
            save: function () {
                axios.post('/api/escolas/' + this.escola.id + '/criarTurma', this.turma).then(response => {
                    this.toastPopUp("success", "Turma Criada!");
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
                this.turma.nome = "";
                this.turma.ciclo = "";
                this.turma.professor = undefined;
            },
            getProfessores(url = 'api/users/professores'){
                axios.get(url)
                    .then(response => {
                        this.professores = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
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
                return this.professores.filter((i) => {
                    return i.escola[0] === this.escola.nome;
                });
            }
        }
    }
    ;

</script>

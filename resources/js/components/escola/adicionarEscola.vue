<template>
    <div @focusout="closeLists">
        <!-- Modal Add Order-->
        <div class="modal fade" id="addEscolaModal" tabindex="-1" role="dialog" aria-labelledby="addEscolaModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content" @click="closeLists">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEscolaModal">Registar Escola</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-text-field id="inputNome"
                                          v-model="escola.nome"
                                          label="Nome"
                                          :rules="[v => !!v || 'Nome é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>

                        <div @click="setOpenList">
                            <v-select
                                ref="select"
                                label="Distrito"
                                v-model="escola.distrito"
                                :items="distritos"
                                :rules="[v => !!v || 'Distrito é obrigatório']"
                                class="input-group--focused"
                                required
                            ></v-select>
                        </div>

                    </div>

                    <div v-if="!isLoading" class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">Registar
                        </button>
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
        data: function () {
            return {
                distritos: ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                escola: {
                    nome: "",
                    distrito: "",
                },
                selAberto:false,
                isLoading: false,
            };
        },
        methods: {
            save: function () {
                this.isLoading=true;
                axios.post('/api/escolas', this.escola).then(response => {
                    this.toastPopUp("success", "Escola Criada!");
                    this.cleanForm();
                    this.$emit('getEscolas');
                    $('#addEscolaModal').modal('hide');
                    this.isLoading=false;

                }).catch(error => {
                    this.isLoading=false;
                    this.toastErrorApi(error);
                })
            },

            cancel: function () {
                this.cleanForm();
                $('#addEscolaModal').modal('hide');
            },

            cleanForm: function () {
                this.escola.nome = "";
                this.escola.distrito = "";
            },

            setOpenList() {
                setTimeout(() => {
                    if (this.$refs.select.isMenuActive == true ) {
                        setTimeout(() => {
                            this.selAberto = true;
                        }, 30);
                    }
                }, 10)
            },

            closeLists() {
                if (this.selAberto == true) {
                    this.selAberto = false;
                    this.$refs.select.isMenuActive = false;
                }

            },
        },

        computed: {
            hasErrors: function () {
                if (!this.escola.nome || !this.escola.distrito) {
                    return true;
                }
                return false;
            }
            ,
        }
    }
    ;

</script>
<style>
    img.preview {
        width: 200px;
        background-color: white;
        border: 1px solid #DDD;
        padding: 5px;
    }
</style>

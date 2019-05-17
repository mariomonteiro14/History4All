<template>
    <div id="app">
        <br><br><br><br>

        <v-app id="inspire">
            <v-layout align-content-center>
                <v-flex xs12 sm7 offset-sm3>
                    <v-card>
                        <v-img v-if="patrimonio.imagens && patrimonio.imagens.length >0"
                               :src="getPatrimonioPhoto(patrimonio.imagens[0].foto)"
                               aspect-ratio="2.5"
                               alt="patrimonio.nome"
                        ></v-img>

                        <v-card-title primary-title>
                            <div>
                                <h3 class="headline mb-0">{{patrimonio.nome}}</h3>
                                <span class="grey--text">{{patrimonio.distrito}}</span>
                                <br><br>
                                <div v-html="patrimonio.descricao"></div>
                            </div>
                        </v-card-title>
                        <v-card-actions>
                            <v-btn flat color="orange" v-if="patrimonio.imagens && patrimonio.imagens.length > 1" @click="showGallery=!showGallery">
                                Galeria
                            </v-btn>
                            <v-btn flat color="green" v-if="$store.state.user && $store.state.user.tipo === 'professor'"
                                   data-toggle="modal" data-target="#adicionarImagemModal">
                                Adicionar Imagens
                            </v-btn>
                        </v-card-actions>

                    </v-card>
                </v-flex>
            </v-layout>
            <br>
            <v-layout v-if="showGallery">

                <v-flex xs12 sm7 offset-sm3>
                    <v-card>
                        <v-container grid-list-sm fluid>
                            <v-layout row wrap>
                                <v-flex v-for="(image, index) in patrimonio.imagens" :key="index" xs4 d-flex>
                                    <v-card flat tile class="d-flex">
                                        <v-img
                                            :src="getPatrimonioPhoto(image.foto)"
                                            aspect-ratio="1"
                                            class="grey lighten-2"
                                        >
                                            <template v-slot:placeholder>
                                                <v-layout
                                                    fill-height
                                                    align-center
                                                    justify-center
                                                    ma-0
                                                >
                                                    <v-progress-circular indeterminate
                                                                         color="grey lighten-5"></v-progress-circular>
                                                </v-layout>
                                            </template>
                                        </v-img>
                                    </v-card>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-app>
            <v-layout align-content-center>
                <v-flex xs1 sm7 offset-sm3>
                    <v-btn flat @click="$router.go(-1)">
                        <h6 class="primary--text">
                            <v-icon>fa fa-arrow-left</v-icon>&nbsp Voltar
                        </h6>
                    </v-btn>
                </v-flex>
            </v-layout>
        <div class="modal fade" id="adicionarImagemModal" tabindex="-1" role="dialog" aria-labelledby="contactModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title">Adicionar Imagens</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="custom-file">
                            <label class="custom-file-label" for="upload-files">{{getFilesText()}}</label>
                            <input id="upload-files" type="file" multiple class="form-control custom-file-input"
                                   @change="handleFile" accept=".png, .jpg, .jpeg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="update" :disabled="attachments.length < 1">Guardar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
    </div>

</template>
<script type="text/javascript">
    export default {
        props: ['id'],
        data: function () {
            return {
                patrimonio: {},
                showGallery: false,
                attachments: [],
            };
        },
        methods: {
            getPatrimonio() {
                axios.get('/api/patrimonios/' + this.id)
                    .then(response => {
                        this.patrimonio = response.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            handleFile: function (e) {
                let files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return false;
                }
                for (let i = 0; i < files.length; i++) {
                    if (!files[i].type.includes("image/")) {
                        this.$toasted.error('File must be an image', {
                            position: "bottom-center",
                            duration: 3000,
                        });
                        return;
                    }
                    this.attachments.push(files[i]);
                }
            },
            formCreate: function () {
                let formData = new FormData();
                for (let i = 0; i < this.attachments.length; i++) {
                    formData.append('novas_imagens[]', this.attachments[i]);
                    console.log(this.attachments[i]);
                }
                return formData;
            },
            update() {
                const config = {
                    headers: {'content-type': 'multipart/form-data'}
                };
                axios.post('/api/patrimonios/' + this.patrimonio.id + '/imagens', this.formCreate(), config).then(response => {
                    this.toastPopUp("success", "Imagens adicionadas con sucesso!");
                    this.attachments = [];
                    $('#adicionarImagemModal').modal('hide');
                    this.patrimonio.imagens = response.data;
                }).catch(function (error) {
                    this.toastPopUp("error", `${error.response.data.message}`);
                    console.log(error);
                });
            },
            cancel: function () {
                this.attachments = [];
                $('#adicionarImagemModal').modal('hide');
            },
            getFilesText() {
                return this.attachments.length == 0 ? "Adicionar Imagens" : this.attachments.length == 1 ?
                    "1 Imagem Carregada" : this.attachments.length + " imagens Carregadas";
            },
        },
        mounted() {
            this.getPatrimonio();
        }
    }
</script>

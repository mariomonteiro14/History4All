<template>
    <div id="app">
        <br><br><br><br>
        <v-app id="inspire">
            <v-layout align-content-center>
                <v-flex offset-sm1 sm1 style="position:fixed; top:15%">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn large color="indigo" v-on="on" icon @click="$router.go(-1)">
                                <v-icon color="white">fa fa-arrow-left</v-icon>
                            </v-btn>
                        </template>
                        <span>Voltar</span>
                    </v-tooltip>
                    <br>
                    <br>
                    <v-tooltip bottom v-if="patrimonio.imagens && patrimonio.imagens.length > 1">
                        <template v-slot:activator="{ on }">
                            <v-btn color="warning" icon large v-on="on" v-scroll-to="'#btnGallery'"
                                   @click="showGallery=true">
                                <v-icon color="white">far fa-image</v-icon>
                            </v-btn>
                        </template>
                        <span>Galeria</span>
                    </v-tooltip>
                    <br>
                    <br>
                    <v-tooltip bottom
                               v-if="patrimonio.id && $store.state.user && $store.state.user.tipo === 'professor'">
                        <template v-slot:activator="{ on }">
                            <v-btn color="green" icon large v-on="on"
                                   data-toggle="modal" data-target="#adicionarImagemModal">
                                <v-icon color="white">fa-upload</v-icon>
                            </v-btn>
                        </template>
                        <span>Adicionar Imagens</span>
                    </v-tooltip>
                </v-flex>

                <v-flex xs12 sm8 offset-sm2>
                    <v-progress-linear v-if="isLoadingPatrimonio" v-slot:progress :color="colorDefault"
                                       indeterminate></v-progress-linear>
                    <v-container v-else-if="!patrimonio.id">
                        <v-alert :value="true" color="error" icon="warning">
                            Património não encontrado :(
                        </v-alert>
                    </v-container>
                    <v-card v-else>
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
                            <v-btn flat color="orange" v-if="patrimonio.imagens && patrimonio.imagens.length > 1"
                                   @click="showGallery=!showGallery" id="btnGallery">
                                Galeria
                            </v-btn>
                        </v-card-actions>

                    </v-card>
                </v-flex>
            </v-layout>
            <br>
            <v-layout v-if="showGallery">

                <v-flex xs12 sm8 offset-sm2>
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

        <div class="modal fade" id="adicionarImagemModal" tabindex="-1" role="dialog" aria-labelledby="contactModal"
             data-keyboard="false" data-backdrop="static" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title">Adicionar Imagens</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoadingImagens">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="custom-file">
                            <label class="custom-file-label" for="upload-files">{{getFilesText()}}</label>
                            <input id="upload-files" type="file" multiple class="form-control custom-file-input"
                                   @change="handleFile" accept=".png, .jpg, .jpeg">
                        </div>
                    </div>
                    <div v-if="!isLoadingImagens" class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="adicionarImagens"
                                :disabled="attachments.length < 1">Guardar
                        </button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
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
                isLoadingPatrimonio: false,
                isLoadingImagens: false,
            };
        },
        methods: {
            getPatrimonio() {
                this.isLoadingPatrimonio = true;
                axios.get('/api/patrimonios/' + this.id)
                    .then(response => {
                        this.isLoadingPatrimonio = false;
                        this.patrimonio = response.data;
                    })
                    .catch(error => {
                        this.isLoadingPatrimonio = false;
                        this.toastErrorApi(error);
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
            adicionarImagens() {
                const config = {
                    headers: {'content-type': 'multipart/form-data'}
                };
                this.isLoadingImagens = true;
                axios.post('/api/patrimonios/' + this.patrimonio.id + '/imagens', this.formCreate(), config).then(response => {
                    this.isLoadingImagens = false;
                    this.toastPopUp("success", "Imagens adicionadas con sucesso!");
                    this.attachments = [];
                    $('#adicionarImagemModal').modal('hide');
                    this.patrimonio.imagens = response.data;
                }).catch(error => {
                    this.toastErrorApi(error);
                    this.isLoadingImagens = false;
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

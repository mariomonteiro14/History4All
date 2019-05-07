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
                                <v-flex v-for="image in patrimonio.imagens" :key="n" xs4 d-flex>
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
            }
        },
        mounted() {
            this.getPatrimonio();
        }
    }
</script>

<template>
    <div>
        <br><br><br>
        <v-container fluid grid-list-lg>
            <v-card id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <v-img aspect-ratio="1.5" class=" w-100 grey lighten-2" :src="getPatrimonioPhoto('001.jpg')"
                               min-height="250" max-height="350">
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
                            <!--<v-container center align-center fluid>
                                <v-layout align-end justify-center row fill-height>
                                    <br><br><br><br><br>
                                    <br><br><br><br><br>
                                    <br><br><br><br><br>
                                    <v-spacer></v-spacer>
                                    <v-flex xs12 sm10 justify-center flexbox>
                                        <h1 class="white--text">A vontade de aprender</h1>
                                    </v-flex>
                                    <v-spacer></v-spacer>
                                </v-layout>
                            </v-container>-->
                        </v-img>
                    </div>
                    <div class="carousel-item">
                        <v-img aspect-ratio="1.5" center class=" grey lighten-2 d-block w-100" :src="getPatrimonioPhoto('002.jpg')"
                               min-height="250" max-height="350">
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
                    </div>
                    <div class="carousel-item">
                        <v-img aspect-ratio="1.5" center class="d-block w-100 grey lighten-2" :src="getPatrimonioPhoto('003.jpg')"
                               min-height="250" max-height="350">
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
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </v-card>
        </v-container>

        <v-container fluid grid-list-lg>
            <v-layout row wrap>
                <v-flex d-flex xs12 sm4>
                    <v-card class="btn" color="green lighten-4" flat>
                        <br>
                        <h3 class="align-content-center">
                            <v-icon large color="green darken-2">business</v-icon>
                            <br>
                            <strong class="headline font-weight-light">Sobre Nós</strong>
                        </h3>

                        <h2 class="font-weight-light">
                            Somos uma plataforma de história interativa para ensino básico e secundário.
                        </h2>
                        <br>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="green darken-2" class="font-weight-regular title" flat round
                                   data-toggle="modal" data-target="#aboutUsModal">
                                Saber Mais
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-flex>
                <v-flex d-flex xs12 sm4>
                    <v-card class="btn" color="light-blue lighten-3" flat>
                        <br>
                        <h3 class="align-content-center">
                            <v-icon large color="green darken-4">forum</v-icon>
                            <br>
                            <strong class="headline font-weight-light" font-weight-bold>Fórum</strong>
                        </h3>

                        <h2 class="font-weight-light">
                            Fórum de discussão sobre os patrimónios
                        </h2>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn class="font-weight-regular title" to="/forums"
                                   round flat color="light-green darken-3">Fórum
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-flex>
                <v-flex d-flex xs12 sm4>
                    <v-card class="btn" color="lime lighten-2" flat>
                        <br>
                        <h3 class="align-content-center">
                            <v-icon large color="teal darken-2">email</v-icon>
                            <br>
                            <strong class="headline font-weight-light" font-weight-bold>Contacte-nos</strong>
                        </h3>

                        <h2 class="font-weight-light">
                            Preencha o formulário e esclareça todas as suas dúvidas. Também poderá solicitar o registo
                            da sua escola na plataforma.
                        </h2>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn class="font-weight-regular title" data-toggle="modal" data-target="#contactModal"
                                   round flat color="light-green darken-3">Formulário
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
        <contact :email-to="''"></contact>
        <about-us></about-us>
    </div>
</template>

<script type="text/javascript">
    import BImg from "bootstrap-vue/es/components/image/img";
    import {Carousel, Slide} from 'vue-carousel';
    import contactForm from './widgets/contactForm.vue'
    import aboutUs from './widgets/aboutUs.vue'

    export default {
        components: {BImg, Carousel, Slide, 'contact': contactForm, 'about-us': aboutUs},
        data: function () {
            return {
                title: "History4All",
            }
        },
        methods: {}
    }
</script>


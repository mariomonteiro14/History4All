<template>
    <div>
        <br><br><br><br>
        <!--<carousel :per-page="1" autoplay autoplayTimeout=7000 class="carousel slide" data-ride="carousel">
            <slide>
                <b-img center class="thumbnail fluid" v-bind:src="getPatrimonioPhoto('1.jpg')"></b-img>
            </slide>
            <slide>
                <b-img center v-bind:src="getPatrimonioPhoto('2.jpg')"></b-img>
            </slide>
            <slide>
                <b-img center v-bind:src="getPatrimonioPhoto('3.jpg')"></b-img>
            </slide>
        </carousel>-->
        <v-card id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <b-img center class="d-block w-100" :src="getPatrimonioPhoto('001.jpg')" alt="First slide"/>
                </div>
                <div class="carousel-item">
                    <b-img center class="d-block w-100" :src="getPatrimonioPhoto('002.jpg')" alt="Second slide"/>
                </div>
                <div class="carousel-item">
                    <b-img center class="d-block w-100" :src="getPatrimonioPhoto('003.jpg')" alt="Third slide"/>
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
        <br>
        <br>
        <br>
        <myfooter></myfooter>
    </div>
</template>

<script type="text/javascript">
    import BImg from "bootstrap-vue/src/components/image/img";
    import { Carousel, Slide } from 'vue-carousel';
    import myfooter from './widgets/foter.vue'

    export default {
        components: {BImg,Carousel, Slide, 'myfooter': myfooter},
        data: function () {
            return {
                title: "History4All",
            }
        },
        methods: {}
    }
</script>


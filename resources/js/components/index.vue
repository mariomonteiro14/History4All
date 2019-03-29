<template>
    <div>
        <carousel :data="data"></carousel>
    </div>

</template>
<style>
    .example-slide {
        align-items: center;
        background-color: #666;
        color: #999;
        display: flex;
        font-size: 1.5rem;
        justify-content: center;
        min-height: 10rem;
    }
</style>

<script type="text/javascript">
    import navigation from './widgets/nav.vue';
    //TODO
    export default {
        components: {
            'navigation': navigation,
        },
        data: function () {
            return {
                title: "History4All",
                data: [
                    '<div class="example-slide">Slide 1</div>',
                    '<div class="example-slide">Slide 2</div>',
                    '<div class="example-slide">Slide 3</div>',
                ],
            }
        },
        methods: {
        }
    }
    </script>


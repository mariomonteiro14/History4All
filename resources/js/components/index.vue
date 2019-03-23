<template>
    <div>
     <navigation></navigation>

    </div>


</template>
<script type="text/javascript">
    import navigation from './navigation.vue';

    export default {
        components: {
            'navigation': navigation,
        },
        data: function () {
            return {
                title: "History4All",
            }
        },
        methods: {
        }
    }
    </script>


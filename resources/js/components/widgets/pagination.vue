<template>
    <nav class="pagination" >
        <span class="btn btn-light disabled">{{pagination.from}} - {{pagination.to}} of {{pagination.total}}</span>
        <a v-if="pagination.prevPageUrl" class="btn btn-info text-white" @click="$emit('prev');">
            Prev
        </a>
        <a class="btn btn-info disabled text-white" v-else :disabled="true">
           Prev
        </a>

        <a v-if="pagination.nextPageUrl" class="btn btn-info text-white" @click="$emit('next');">
            Next
        </a>
        <a class="btn btn-info disabled text-white" v-else :disabled="true">
            Next
        </a>
    </nav>

</template>
<script>
	export default{
		props: ['pagination']
	}
</script>
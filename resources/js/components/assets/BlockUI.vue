<template>
    <v-overlay :value="$store.state.overlay" z-index="100000">
        <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>
</template>

<script>
export default {
    name: "BlockUI",
}
</script>

<style scoped>

</style>

<template>
    <table class="table table-bordered table-sm">
        <thead class="table-dark">
        <tr v-if="tableName">
            <th colspan="7" class="text-center">
                <inertia-link v-if="tableNameLink" :href="tableNameLink">{{ tableName }}</inertia-link>
                <span v-else>{{ tableName }}</span>
            </th>
        </tr>
        <tr>
            <th v-for="column in columns" :class="column.class">
                {{ column.name }}
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="tableData.length <= 0">
            <td :colspan="columns.length" class="text-center">{{ this.__('[No data]') }}</td>
        </tr>
        <tr
            v-for="(row, index) in tableData"
            v-bind:style="[(index % 2) === 0 && !isHover[index] ? 'background-color: rgba(0, 0, 0, 0.05);' : '']"
            @mouseenter="isHover[index]=true;"
            @mouseleave="isHover[index]=false;"
            :class="{ 'bg-blue-100' : isHover[index] }"
        >
            <td v-for="column in columns" v-html="row[column.showField]">
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name: "SimpleTable",
    props: {
        'tableData': String,
        'tableName': String,
        'tableNameLink': String,
        'columns': {
            'name': String,
            'class': String,
            'showField': String
        }
    },
    data() {
        return {
            isHover: []
        }
    }
}
</script>

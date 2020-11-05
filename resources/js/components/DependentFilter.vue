<template>
    <div v-show="loading || !filter.hideWhenEmpty || availableOptions.length > 0">
        <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">{{ filter.name }}</h3>

        <div class="p-2">
            <select-control
                :dusk="`${filter.name}-filter-select`"
                class="block w-full form-control-sm form-select"
                :value="value"
                @change="handleChange($event.target.value)"
                :options="availableOptions"
                :label="optionValue"
                :selected="value"
            >
                <option value="" selected>&mdash;</option>
            </select-control>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        resourceName: {
            type: String,
            required: true,
        },
        lens: String,
        filterKey: {
            type: String,
            required: true,
        },
    },

    data: () => ({
        options: [],
        loading: false,
        resourceName: '',
    }),

    created() {
        this.options = this.filter.options
        this.$watch(() => {
            this.loading = true;

            this.fetchOptions(this.filter.dependentOf.reduce((r, filter) => {
                r[filter] = this.$store.getters[`${this.resourceName}/getFilter`](filter).currentValue;
                return r;
            }, {}));
        });
    },

    methods: {
        handleChange(value) {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value: value,
            })

            this.$emit('change')
        },

        optionValue(option) {
            return option.label || option.name || option.value
        },

        async fetchOptions(filters) {
            const lens = this.lens ? `/lens/${this.lens}` : ''
            this.$data.resourceName = this.resourceName
            const url = `/nova-api/${this.resourceName}${lens}/filters/options`
            console.log(url)
            const {data: options} = await Nova.request().get(url, {
                params: {
                    filters: btoa(JSON.stringify(filters)),
                    filter: this.filterKey,
                },
            }).then(function (options) {
                this.options = options
                console.log("accept")
                console.log(options)

            }, function () {
                console.log("reject")
                // TODO I have no more this so no resourceName, and besides, I don't have the view name...
                // const url = `/nova-vendor/nova-dashboard/${this.resourceName}/views/admin-view/filters`
                // const {data: options} = await Nova.request().get(url, {
                //     params: {
                //         filters: btoa(JSON.stringify(filters)),
                //         filter: this.filterKey,
                //     },
                // })
                // this.options = options
            })

            this.options = options
            this.loading = false
        }
    },

    computed: {
        filter() {
            return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
        },

        value() {
            return this.filter.currentValue
        },

        availableOptions() {
            let options = _.filter(this.options, option => {
                return !option.hasOwnProperty('depends') || _.every(option.depends, (values, filterName) => {
                    const filter = this.$store.getters[`${this.resourceName}/getFilter`](filterName)
                    if (!filter) return true
                    return _.intersection(
                        _.castArray(filter.currentValue).map(String),
                        _.castArray(values).map(String)
                    ).length > 0;
                })
            })
            if (!this.loading && this.value !== '' && options.filter(option => option.value == this.value).length === 0) {
                this.handleChange('')
            }
            return options
        },
    },
}
</script>

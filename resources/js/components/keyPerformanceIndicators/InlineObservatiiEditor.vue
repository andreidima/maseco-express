<template>
    <div class="d-flex align-items-start">
        <!-- Icon in front of the textarea -->
        <span class="status-icon me-2">
            <i v-if="loading" class="fas fa-spinner fa-spin text-primary"></i>
            <i v-else-if="showCheckMark" class="fas fa-check-circle text-success"></i>
        </span>
        <!-- Auto-resizing textarea -->
        <textarea
            ref="textarea"
            class="form-control"
            v-model="localObservatii"
            @input="autoResize"
            @blur="updateObservatii"
            rows="1"
        ></textarea>
    </div>
</template>

<script>
export default {
    props: {
        kpiId: {
            type: Number,
            required: true,
        },
        userId: {
            type: Number,
            required: true,
        },
        observatii: {
            type: String,
            required: true,
        },
        searchMonth: {
            type: Number,
            required: true,
        },
        searchYear: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            localObservatii: this.observatii, // Local copy for editing
            loading: false, // Indicates update is in progress
            showCheckMark: false, // Indicates success after update
        };
    },
    methods: {
        autoResize(event = null) {
            const textarea = event ? event.target : this.$refs.textarea; // Get the textarea element
            textarea.style.height = 'auto'; // Reset height to calculate new size
            textarea.style.height = `${textarea.scrollHeight}px`; // Set height to scroll height
        },
        updateObservatii() {
            this.loading = true;
            this.showCheckMark = false;

            const payload = {
                user_id: this.userId, // Required to identify the user
                month: this.searchMonth, // Specify the month for the KPI
                year: this.searchYear, // Specify the year for the KPI
                observatii: this.localObservatii, // The observation to be updated
            };

            axios
                .post(`/key-performance-indicators/update-observatii`, payload)
                .then(() => {
                    this.loading = false;
                    this.showCheckMark = true;
                    setTimeout(() => {
                        this.showCheckMark = false;
                    }, 5000);
                })
                .catch((error) => {
                    console.error('Error updating observatii:', error);
                    this.loading = false;
                });
        },
    },
    mounted() {
        this.autoResize(); // Adjust the textarea height when the component is mounted
    },
};
</script>

<style scoped>
textarea {
    resize: none; /* Disable manual resizing for a cleaner experience */
    overflow: hidden; /* Prevent scrollbars from appearing */
}
.status-icon {
    font-size: 1.2rem;
    width: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>

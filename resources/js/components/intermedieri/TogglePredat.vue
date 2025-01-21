<template>
    <button @click="togglePredat" class="btn-toggle-predat border-0 flex">
        <span :class="badgeClass">{{ badgeText }}</span>
    </button>
</template>

<script>
export default {
    props: {
        comandaId: {
            type: Number,
            required: true
        },
        initialStatus: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            predatStatus: this.initialStatus,
        };
    },
    computed: {
        badgeText() {
            // console.log('badgeText recalculated:', this.predatStatus);
            return this.predatStatus ? 'DA' : 'NU'; // Check boolean value
        },
        badgeClass() {
            // console.log('badgeClass recalculated:', this.predatStatus);
            return this.predatStatus ? 'badge bg-success' : 'badge bg-danger'; // Check boolean value
        }
    },
    methods: {
        async togglePredat() {
            try {
                const response = await fetch(`/intermedieri/schimbaPredatLaContabilitate/${this.comandaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                // console.log('Server Response:', data); // Log the full response

                if (data.success) {
                    console.log('Toggling Status:', data.predat_la_contabilitate);
                    this.predatStatus = data.predat_la_contabilitate;
                } else {
                    alert('Error updating status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the status.');
            }
        }
    }
};
</script>

<style scoped>
    /* Remove border and background */
    button {
        border: none;
        background: none;
        outline: none; /* Optional: Remove focus outline */
        cursor: pointer; /* Ensures button remains clickable */
        margin-top: 2px ;
    }

    button:hover {
        opacity: 0.8; /* Dim slightly on hover */
    }
    button:focus {
        outline: 2px solid blue; /* Adds a custom focus indicator */
    }
    .flex {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

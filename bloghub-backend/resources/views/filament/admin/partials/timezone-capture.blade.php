<script>
    (function () {
        try {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (!tz) return;

            const sentKey = 'admin_timezone_sent';
            const lastSent = sessionStorage.getItem(sentKey);

            if (lastSent === tz) return;
            sessionStorage.setItem(sentKey, tz);

            fetch(@json(route('admin.timezone')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Timezone': tz,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? @json(csrf_token()),
                },
                body: JSON.stringify({ timezone: tz }),
                credentials: 'same-origin',
                keepalive: true,
            });
        } catch (e) {}
    })();
</script>

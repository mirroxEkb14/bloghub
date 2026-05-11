FROM node:20-alpine
WORKDIR /app

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sh", "-lc", "npm install && npm run dev -- --host 0.0.0.0 --port 5174"]

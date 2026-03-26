FROM node:20-alpine

WORKDIR /app

# Instala yarn globalmente (já vem no node:20-alpine, mas garante)
RUN corepack enable

# Copia arquivos de dependência primeiro (cache de build)
COPY package.json yarn.lock* ./

# Instala dependências
RUN yarn install

# Copia o restante do código
COPY . .

EXPOSE 5173

CMD ["yarn", "dev", "--host", "0.0.0.0"]

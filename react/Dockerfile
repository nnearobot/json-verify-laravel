FROM node:latest

WORKDIR /app

COPY package.json .
COPY . .

RUN npm install

EXPOSE 4173

CMD ["npm", "run", "start"]

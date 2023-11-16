FROM node:20

RUN mkdir /usr/src/verify
WORKDIR /usr/src/verify

COPY . .
COPY .env ./.env

RUN npm install

EXPOSE 4173

CMD ["npm", "start"]

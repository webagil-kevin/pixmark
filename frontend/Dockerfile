# Use an official Node.js image
FROM node:22

# Set working directory
WORKDIR /app

# Install dependencies
COPY package.json package-lock.json* yarn.lock* ./
RUN if [ -f yarn.lock ]; then yarn install; else npm install; fi

# Copy the rest of the application code
COPY . .

# Expose port 3000
EXPOSE 3000

FROM docker.io/library/maven:3.9.11-eclipse-temurin-25-alpine AS build
WORKDIR /app
COPY pom.xml .
COPY src src
RUN mvn -q -DskipTests package

FROM docker.io/library/eclipse-temurin:25-jre-alpine
WORKDIR /app
COPY --from=build /app/target/user-service-*.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-jar", "/app/app.jar"]

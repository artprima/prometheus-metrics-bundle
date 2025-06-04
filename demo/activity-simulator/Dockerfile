FROM alpine:latest

# Install curl and bash
RUN apk add --no-cache curl bash

# Copy simulation script
COPY simulate-activity.sh /simulate-activity.sh
RUN chmod +x /simulate-activity.sh

# Run the simulation
CMD ["/simulate-activity.sh"]

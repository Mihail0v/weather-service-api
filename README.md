# Weather Service

## Quick Start

### 1. Start the application
```bash
make setup
```

### 2. Test the API
```bash
# Check health
curl http://localhost:8080/api/v1/health

# Get weather for a city
curl http://localhost:8080/api/v1/weather/Sofia
```

### 3. Run tests
```bash
make test
```

## API response

```json
{
  "city": "Sofia",
  "temperature": 4.0,
  "trend": "static",
  "formatted_temperature": "4.0 -",
  "recorded_at": "2025-11-14 10:00:00"
}
```

**Trend indicators:**
- 🥵 = hotter than average
- 🥶 = colder than average
- \- = normal

## Make commands

```bash
make up        # Start containers
make down      # Stop containers
make test      # Run tests
make shell     # Enter PHP container
make logs      # View logs
```

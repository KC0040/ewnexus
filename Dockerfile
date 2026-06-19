FROM python:3.11-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# 複製所有靜態資源和頁面
COPY server.py .
COPY assets/ ./assets/
COPY lang/ ./lang/
COPY demos/ ./demos/
COPY clients/ ./clients/

# HTML 頁面
COPY index.html .
COPY about.html .
COPY how-it-works.html .
COPY pricing.html .
COPY cases.html .
COPY contact.html .
COPY privacy.html .
COPY contract.html .

EXPOSE 8000

CMD ["python", "server.py"]

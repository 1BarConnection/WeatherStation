/**
  * A brief description:
  * The device connects to a WiFi network
  * then collects data from sensors and sends
  * them to MySQL database on web server.
  * After sending, the device goes into sleep mode.
  *
  * Prepared by 1BarConnection.
  *
  */

#include <Arduino.h>

// Set password to "" for open networks.
char ssid[] = "ssid";
char pass[] = "password";

// Server details
// The server variable can be just a domain name or it can have a subdomain.
// Depending on the service you are using
const char server[] = "weather.example.com"; // domain name: example.com, maker.ifttt.com, and so on...
const char resource[] = "/post-data2.php";         // source path, for example: /post-data.php
const int  port = 80;                             // server port number

// The value of apiKeyValue must be the same as in the PHP file /post-data.php
String apiKeyValue = "00f2d801-371a-22fb-44d8-8b1e-006e056cba00";

// BME280 pins
#define I2C_SDA_2            4
#define I2C_SCL_2            5
// GY1145 pins
#define I2C_SDA_3            4
#define I2C_SCL_3            5
// MAX17043 pins
#define I2C_SDA_4            4
#define I2C_SCL_4            5

// 1013.25 is sea level pressure value in hPA
#define SEALEVELPRESSURE_HPA (1013.25)

// Serial monitor, 115200 bauds
#define SerialMon Serial
// For AT commands (SIM800 module)
#define SerialAT Serial1

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Adafruit_SI1145.h>
#include <SparkFun_MAX1704x_Fuel_Gauge_Arduino_Library.h>
#include <ESP8266WiFi.h>
#include <ArduinoHttpClient.h>

// I2C for BME280 sensor
TwoWire I2CBME = TwoWire();
Adafruit_BME280 bme;

// I2C for GY1145 sensor
TwoWire I2CGY = TwoWire();
Adafruit_SI1145 gy;

// I2C for MAX17043 sensor
TwoWire I2CMAX = TwoWire();
SFE_MAX1704X lipo;
double soc = 0; // State-of-charge (SOC) battery charge variable

WiFiClient espClient;

HttpClient client = HttpClient(espClient, server, port);

void setup() {
  // Serial (debug) monitor at 115200 baud
  SerialMon.begin(115200);

  // Starting I2C communication
  I2CBME.begin(I2C_SDA_2, I2C_SCL_2);
  I2CGY.begin(I2C_SDA_3, I2C_SCL_3);
  I2CMAX.begin(I2C_SDA_4, I2C_SCL_4);

  // Check the BME280 I2C address, in our case it is 0x76
  if (!bme.begin(0x76, &I2CBME)) {
    Serial.println("No BME280 sensor found, check connection!");
    while (1);
  }

  // Check the GY1145 I2C address, in our case it is 0x60
  if (!gy.begin(0x60, &I2CGY)) {
    Serial.println("GY1145 sensor not found, check connection!");
    while (1);
  }

  lipo.enableDebugging(); // For serial monitor

  // MAX17044:
  if (lipo.begin() == false) // Start MAX17044 on the wire port setting
  {
    Serial.println(F("MAX17043 sensor not found, check connection!"));
    while (1);
  }

  // Perform a reset for the MAX17043 battery charge gauge
  lipo.reset();
  // Quick start restart MAX17043 to get more accurate SOC
	lipo.quickStart();

}

void loop() {
  // Wi-Fi connection
  WiFi.begin(ssid,pass); // Add the Wi-Fi name and password we defined at the beginning
  Serial.print("Connecting to Wi-Fi");
  while(WiFi.status() != WL_CONNECTED){
    delay(1000);
    Serial.print(".");
  }

 if (WiFi.status() != WL_CONNECTED) {
   SerialMon.println(" not successfully");
 }
 else {
  Serial.println();
  Serial.print("Related, IP address:");
  Serial.println(WiFi.localIP());
  Serial.println();

  SerialMon.println("Reading from sensors ...");

  // Quick start restart MAX17043 to get more accurate SOC
	lipo.quickStart();

  float UVindex = gy.readUV();
  // The UV index is multiplied by 100 by default to divide the true value by 100
  UVindex /= 100.0;

  // lipo.getSOC() returns "state of charge" batteries (e.g. 79%)
  soc = lipo.getSOC();

  // Print data
  SerialMon.println("&value2=" + String(bme.readHumidity()) + "&value1=" + String(bme.readTemperature()) +
  "&value3=" + String(bme.readPressure()/100.0F) + "&value4=" + String(UVindex) + "&value5=" + String(soc) +
  "&value6=" + String(bme.readAltitude(SEALEVELPRESSURE_HPA)) + "");
  SerialMon.println();

  SerialMon.print("Connecting to the server ");
  SerialMon.print(server);
  if (!client.connect(server, port)) {
    SerialMon.println(" not successfully");
  }
  else {
    SerialMon.println("The connection to the server is OK");

  // Make an HTTP POST request
  SerialMon.println("Executing HTTP POST request ...");
  String postData = "api_key=" + apiKeyValue +
  "&value2=" + String(bme.readHumidity()) + "&value1=" + String(bme.readTemperature()) +
  "&value3=" + String(bme.readPressure()/100.0F) + "&value4=" + String(UVindex) + "&value5=" + String(soc) +
  "&value6=" + String(bme.readAltitude(SEALEVELPRESSURE_HPA)) + "";

  client.beginRequest();
  client.post(resource);
  client.sendHeader(HTTP_HEADER_CONTENT_TYPE, "application/x-www-form-urlencoded");
  client.sendHeader(HTTP_HEADER_CONTENT_LENGTH, postData.length());
  client.sendHeader("X-CUSTOM-HEADER", "custom_value");
  client.endRequest();
  client.write((const byte*)postData.c_str(), postData.length());
  // Note: the top line of the code can be (easier) written as:
  //client.print(postData);

  // Get a response to HTTP POST (200 if OK)
  int statusCode = client.responseStatusCode();
  String response = client.responseBody();

  Serial.print("POST Status code: ");
  Serial.println(statusCode);
  Serial.print("POST Response: ");
  Serial.println(response);

  // Close the client and disconnect
  client.stop();
  SerialMon.println("The connection to the server is closed");
  //delay(900000);
  Serial.println("Going to sleep ...");
  // Connect D0 and RST pins on Wemos to enable deep sleep
  // 60e6 = 1 min
  //ESP.deepSleep(60e6);
  // 300e6 = 5 min
  //ESP.deepSleep(300e6);
  // 900e6 = 15 min
  ESP.deepSleep(900e6);
  // 1800e6 = 30 min
  //ESP.deepSleep(1800e6);

  }
 }
}

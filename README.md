# 🚀 Containerized PHP Web App Deployment on AWS ECS Fargate

[![AWS](https://img.shields.io/badge/AWS-232F3E?style=for-the-badge&logo=amazon-aws&logoColor=white)](https://aws.amazon.com/)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

A highly available, containerized PHP web application deployed on **Amazon ECS Fargate** using **Docker**, **Amazon ECR**, **Application Load Balancer (ALB)**, **Amazon S3**, and a custom **AWS VPC**.

The application provides a web interface that allows users to upload files. Uploaded files are securely passed from the PHP web container in private subnets and stored directly in an **Amazon S3 bucket** using IAM Task Roles.

---

## 📋 Table of Contents

- [ Architectural Overview](#-architectural-overview)
- [🌐 Network & Subnet Topology](#-network--subnet-topology)
- [🛠️ Technologies Used](#️-technologies-used)
- [📁 Project Structure](#-project-structure)
- [📋 Prerequisites](#-prerequisites)
- [🚀 Deployment Step-by-Step](#-deployment-step-by-step)
  - [Step 1: Clone the Repository](#step-1--clone-the-repository)
  - [Step 2: Build the Docker Image](#step-2--build-the-docker-image)
  - [Step 3: Test the Application Locally](#step-3--test-the-application-locally)
  - [Step 4: Create Amazon ECR Repository](#step-4--create-amazon-ecr-repository)
  - [Step 5: Authenticate Docker with ECR](#step-5--authenticate-docker-with-ecr)
  - [Step 6: Tag the Docker Image](#step-6--tag-the-docker-image)
  - [Step 7: Push Docker Image to ECR](#step-7--push-docker-image-to-ecr)
  - [Step 8: Create the AWS VPC Subnets](#step-8--create-the-aws-vpc-subnets)
  - [Step 9: Create and Configure Internet Gateway](#step-9--create-and-configure-internet-gateway)
  - [Step 10: Create and Configure NAT Gateway](#step-10--create-and-configure-nat-gateway)
  - [Step 11: Configure Security Groups](#step-11--configure-security-groups)
  - [Step 12: Create Amazon S3 Bucket](#step-12--create-amazon-s3-bucket)
  - [Step 13: Configure IAM Roles](#step-13--configure-iam-roles)
  - [Step 14: Create ECS Cluster](#step-14--create-ecs-cluster)
  - [Step 15: Create ECS Task Definition](#step-15--create-ecs-task-definition)
  - [Step 16: Provision Application Load Balancer](#step-16--provision-application-load-balancer)
  - [Step 17: Create Target Group](#step-17--create-target-group)
  - [Step 18: Deploy ECS Service](#step-18--deploy-ecs-service)
  - [Step 19: Configure ECS Auto Scaling](#step-19--configure-ecs-auto-scaling)
  - [Step 20: Verify Deployment](#step-20--verify-deployment)
  - [Step 21: Access Application via ALB](#step-21--access-application-via-alb)
  - [Step 22: End-to-End File Upload Test](#step-22--end-to-end-file-upload-test)
- [🔐 Security Architecture](#-security-architecture)
- [🔄 Docker & ECS Image Flow](#-docker--ecs-image-flow)
- [📚 Key Learnings](#-key-learnings)
- [🔮 Future Improvements](#-future-improvements)
- [👨‍💻 Author](#-author)

---

## 🏗️ Architectural Overview

```text
                                     Internet
                                        │
                                        ▼
                            ┌───────────────────────┐
                            │ Application Load      │
                            │ Balancer (Port 80)    │
                            └───────────┬───────────┘
                                        │
                 ┌──────────────────────┴──────────────────────┐
                 │                                             │
                 ▼                                             ▼
      ┌──────────────────────┐                      ┌──────────────────────┐
      │   Public Subnet 1    │                      │   Public Subnet 2    │
      │   192.168.0.0/26     │                      │   192.168.0.64/26    │
      │  ┌────────────────┐  │                      │                      │
      │  │  NAT Gateway   │  │                      │                      │
      │  └───────┬────────┘  │                      │                      │
      └──────────┼───────────┘                      └──────────────────────┘
                 │ Outbound Traffic
                 ▼
      ┌──────────────────────┐                      ┌──────────────────────┐
      │   Private Subnet 1   │                      │   Private Subnet 2   │
      │   192.168.0.128/26   │                      │   192.168.0.192/26   │
      │  ┌────────────────┐  │                      │  ┌────────────────┐  │
      │  │  ECS Fargate   │  │                      │  │  ECS Fargate   │  │
      │  │  Task (Container) │                      │  │  Task (Container) │
      │  └───────┬────────┘  │                      │  └───────┬────────┘  │
      └──────────┼───────────┘                      └──────────┼───────────┘
                 │                                             │
                 └──────────────────────┬──────────────────────┘
                                        │ File Upload (S3 SDK / IAM Task Role)
                                        ▼
                            ┌───────────────────────┐
                            │   Amazon S3 Bucket    │
                            │  `ecs-project-bucket` │
                            └───────────────────────┘
```

---

## 🌐 Network & Subnet Topology

- **AWS Region:** `us-east-1` (US East - N. Virginia)
- **VPC Address Space:** `192.168.0.0/24`

| Subnet Name | CIDR Block | Type | Availability Zone | Primary Resources & Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **Public Subnet 1** | `192.168.0.0/26` | Public | `us-east-1a` | ALB, NAT Gateway |
| **Public Subnet 2** | `192.168.0.64/26` | Public | `us-east-1b` | ALB |
| **Private Subnet 1** | `192.168.0.128/26` | Private | `us-east-1a` | ECS Fargate Containers |
| **Private Subnet 2** | `192.168.0.192/26` | Private | `us-east-1b` | ECS Fargate Containers |

---

## 🛠️ Technologies Used

### Application & Tools
- **PHP** — Web application logic
- **Composer** — PHP dependency management
- **Docker** — Application containerization

### AWS Cloud Infrastructure
- **Amazon ECS (Fargate)** — Serverless container orchestration
- **Amazon ECR** — Docker image registry
- **Amazon S3** — Object storage for uploaded files
- **Application Load Balancer (ALB)** — Layer 7 load balancing
- **Amazon VPC** — Custom isolated cloud network
- **Internet Gateway (IGW)** — Inbound/outbound public internet connectivity
- **NAT Gateway** — Outbound internet connectivity for private containers
- **AWS IAM** — Task Roles and Execution Roles for fine-grained security
- **Security Groups & Route Tables** — Virtual firewalls and traffic routing
- **ECS Service Auto Scaling** — Dynamic scaling between 2 and 10 tasks

---

## 📁 Project Structure

```text
aws-ecs-web-app-deployment/
├── Dockerfile            # Container build specification
├── README.md             # Project documentation
├── composer.json         # PHP project dependencies
├── composer.lock         # Locked dependency version specifications
├── composer-setup.php    # Setup script for Composer installer
├── index.php             # Web app frontend interface
└── upload.php            # File upload handler & S3 integration script
```

---

## 📋 Prerequisites

Before proceeding, ensure you have the following installed and configured:

1. **AWS Account & Credentials:** [Create an AWS Account](https://aws.amazon.com/free/) if needed.
2. **AWS CLI:** Installed and configured with your account credentials:
   ```bash
   aws --version
   aws configure
   aws sts get-caller-identity
   ```
3. **Docker Engine / Desktop:** Installed and running:
   ```bash
   docker --version
   ```
4. **Git CLI:** Installed:
   ```bash
   git --version
   ```

---

## 🚀 Deployment Step-by-Step

### Step 1 — Clone the Repository
```bash
git clone https://github.com/muzammalali2011/aws-ecs-web-app-deployment.git
cd aws-ecs-web-app-deployment
```

### Step 2 — Build the Docker Image
Build the local container image:
```bash
docker build -t php-web-app .
```
Verify local image creation:
```bash
docker images
```

### Step 3 — Test the Application Locally
Run the application container on port `80`:
```bash
docker run -p 80:80 php-web-app
```
Open your web browser and navigate to:
```text
http://localhost
```
Test the file upload functionality locally. Once verified, stop the running container:
```bash
docker ps
docker stop <container-id>
```

---

### Step 4 — Create Amazon ECR Repository
1. Navigate to **AWS Console** → **Amazon ECR** → **Repositories**.
2. Click **Create repository** and name it:
   ```text
   php-web-app
   ```
3. Copy your repository URI (Format: `<ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/php-web-app`).

---

### Step 5 — Authenticate Docker with ECR
Authenticate your local Docker client with your AWS ECR registry in `us-east-1`:
```bash
aws ecr get-login-password --region us-east-1 | docker login   --username AWS   --password-stdin <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com
```
*Output expected:* `Login Succeeded`

---

### Step 6 — Tag the Docker Image
Tag your local `php-web-app:latest` image to match your ECR repository repository URL:
```bash
docker tag php-web-app:latest   <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/php-web-app:latest
```

---

### Step 7 — Push Docker Image to ECR
Push the container image to Amazon ECR:
```bash
docker push <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/php-web-app:latest
```

---

### Step 8 — Create the AWS VPC Subnets
1. Open **AWS VPC Console** → **Create VPC**.
2. Set IPv4 CIDR block: `192.168.0.0/24`.
3. Create 4 subnets across two Availability Zones:
   - **Public Subnet 1:** `192.168.0.0/26` | AZ: `us-east-1a`
   - **Public Subnet 2:** `192.168.0.64/26` | AZ: `us-east-1b`
   - **Private Subnet 1:** `192.168.0.128/26` | AZ: `us-east-1a`
   - **Private Subnet 2:** `192.168.0.192/26` | AZ: `us-east-1b`

---

### Step 9 — Create and Configure Internet Gateway
1. Create an **Internet Gateway** (IGW) and attach it to your VPC.
2. Edit the **Public Route Table**:
   - Add Route: `0.0.0.0/0` → Target: **Internet Gateway**.
3. Associate Public Route Table with **Public Subnet 1** and **Public Subnet 2**.

---

### Step 10 — Create and Configure NAT Gateway
1. Provision a **NAT Gateway** in **Public Subnet 1** (`192.168.0.0/26`).
2. Allocate and attach an Elastic IP address.
3. Edit the **Private Route Table**:
   - Add Route: `0.0.0.0/0` → Target: **NAT Gateway**.
4. Associate Private Route Table with **Private Subnet 1** and **Private Subnet 2**.

---

### Step 11 — Configure Security Groups

Create two security groups within the VPC:

1. **`ALB-SG` (Application Load Balancer Security Group)**
   - **Inbound Rule:** HTTP (Port 80) | Source: `0.0.0.0/0` (Anywhere)

2. **`ECS-SG` (ECS Task Security Group)**
   - **Inbound Rule:** HTTP (Port 80) | Source: Custom → `ALB-SG`

```text
Internet  ──▶  [ ALB-SG ]  ──▶  Application Load Balancer  ──▶  [ ECS-SG ]  ──▶  ECS Fargate Tasks
```

---

### Step 12 — Create Amazon S3 Bucket
1. Go to **AWS S3 Console** → **Create bucket**.
2. Name the bucket:
   ```text
   ecs-project-bucket
   ```
3. Keep default settings (Block public access enabled; access will be managed via IAM Task Role).

---

### Step 13 — Configure IAM Roles

Create two IAM roles in AWS IAM Console:

1. **Task Role (`s3-access-role`):**
   - Attached to the ECS Task container execution.
   - Provides read/write access to `ecs-project-bucket` so `upload.php` can write files directly to S3.
2. **Task Execution Role (`ecsTaskExecutionRole`):**
   - Managed by AWS. Allows ECS Fargate agent to pull images from ECR and send logs to Amazon CloudWatch.

---

### Step 14 — Create ECS Cluster
1. Open **Amazon ECS** → **Clusters** → **Create Cluster**.
2. Cluster Name: `my-fargate-cluster`.
3. Infrastructure choice: **AWS Fargate (serverless)**.

---

### Step 15 — Create ECS Task Definition
1. Open **ECS** → **Task Definitions** → **Create new Task Definition**.
2. Configure attributes:
   - **Task Definition Name:** `My-Web`
   - **Launch Type:** AWS Fargate
   - **CPU:** `1 vCPU`
   - **Memory:** `2 GB`
   - **Network Mode:** `awsvpc`
   - **Task Role:** `s3-access-role`
   - **Task Execution Role:** `ecsTaskExecutionRole`
3. Add Container details:
   - **Container Name:** `php-web-app`
   - **Image URI:** `<ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/php-web-app:latest`
   - **Port Mapping:** `80` (HTTP)

---

### Step 16 — Provision Application Load Balancer
1. Go to **EC2 Console** → **Load Balancers** → **Create Application Load Balancer**.
2. Scheme: **Internet-facing**.
3. Select VPC and assign subnets: **Public Subnet 1** & **Public Subnet 2**.
4. Attach Security Group: **`ALB-SG`**.
5. Add Listener: HTTP on Port 80.

---

### Step 17 — Create Target Group
1. Go to **EC2 Console** → **Target Groups** → **Create Target Group**.
2. Target Type: **IP addresses**.
3. Protocol: HTTP | Port: 80 | VPC: Custom VPC.
4. Set Health Check path to `/` (index.php).

---

### Step 18 — Deploy ECS Service
1. Open **ECS** → **Clusters** → `my-fargate-cluster` → **Services** → **Create**.
2. Task Definition: `My-Web`.
3. Desired Tasks: `2`.
4. Networking:
   - Subnets: Select **Private Subnet 1** and **Private Subnet 2**.
   - Security Group: Attach **`ECS-SG`**.
   - Auto-assign Public IP: **DISABLED**.
5. Load Balancing: Connect service to the created ALB Target Group.

---

### Step 19 — Configure ECS Auto Scaling
1. Edit ECS Service → **Service Auto Scaling**.
2. Configure parameters:
   - **Minimum Tasks:** `2`
   - **Maximum Tasks:** `10`
3. Configure target tracking policy based on CPU utilization or HTTP request count.
4. *(Optional Testing)* Executed scheduled scaling policy test scaling tasks from `2` ➔ `4` ➔ `2` to confirm elasticity.

---

### Step 20 — Verify Deployment
1. Go to **Amazon ECS Console** → **Clusters** → `my-fargate-cluster` → **Tasks**.
2. Verify both tasks are in state `RUNNING`.
3. Go to **EC2 Console** → **Target Groups** → **Targets**.
4. Confirm both container target IPs show status: `Healthy`.

---

### Step 21 — Access Application via ALB
1. Navigate to **EC2 Console** → **Load Balancers**.
2. Select your Application Load Balancer and copy the **DNS name**.
3. Paste the ALB URL into your browser:
   ```text
   http://<ALB-DNS-NAME>
   ```

---

### Step 22 — End-to-End File Upload Test
1. Access the web interface at `http://<ALB-DNS-NAME>`.
2. Choose a file and click **Upload**.
3. Open **Amazon S3 Console** → **Buckets** → `ecs-project-bucket`.
4. Confirm that the uploaded object is listed inside the S3 bucket.

---

## 🔐 Security Architecture

- **Private Subnet Isolation:** Tasks run inside private subnets without public IPs. They cannot be targeted directly from the internet.
- **Strict Inbound Filtering:** ECS containers accept HTTP traffic exclusively originating from `ALB-SG`.
- **Credential-less S3 Access:** The PHP application leverages **AWS IAM Task Roles** (`s3-access-role`) via the AWS SDK for PHP. No AWS access keys or secret keys are hardcoded into the application code or Docker containers.

---

## 🔄 Docker & ECS Image Flow

```text
  Local PHP Code
        │
        ▼
   Dockerfile
        │
        ▼
   Docker Image
        │
        ▼
   Amazon ECR  (Repository: php-web-app:latest)
        │
        ▼
  ECS Task Definition (My-Web)
        │
        ▼
  ECS Fargate Tasks (Private Subnets 1 & 2)
```

---

## 📚 Key Learnings

Through building and deploying this infrastructure, I gained practical hands-on experience in:

- 🐳 **Containerization:** Packaging PHP web applications and runtime environments with Docker.
- 📦 **Image Registry Management:** Managing push/pull lifecycle using Amazon ECR.
- 🚀 **Serverless Containers:** Orchestrating scalable microservices via Amazon ECS Fargate.
- 🌐 **Cloud Networking:** Designing custom multi-AZ VPC networks with public/private subnet segmentation.
- 🔄 **Outbound Connectivity:** Setting up NAT Gateways and custom Route Tables for private subnet resource updates.
- ⚖️ **Load Balancing & Elasticity:** Provisioning Application Load Balancers, Target Groups, and ECS Service Auto Scaling.
- 🔑 **Cloud Security:** Enforcing least privilege access using AWS IAM Task Roles and Security Group referencing.

---

## 🔮 Future Improvements

- [ ] **HTTPS / Encryption in Transit:** Configure AWS Certificate Manager (ACM) with an SSL/TLS certificate.
- [ ] **Custom Domain Name:** Link domain name via Amazon Route 53.
- [ ] **CI/CD Pipeline:** Automate application updates and deployments using GitHub Actions or AWS CodePipeline.
- [ ] **Infrastructure as Code (IaC):** Recreate network and compute resources using Terraform or AWS CloudFormation.
- [ ] **Application Security:** Integrate AWS WAF (Web Application Firewall) in front of the ALB.
- [ ] **Monitoring & Observability:** Configure CloudWatch Alarms for CPU/Memory metrics and ALB target health.

---

## 👨‍💻 Author

**Muzammal Ali**  
*Cybersecurity Student | Cloud & DevOps Enthusiast*

- **Focus Areas:** AWS Architecture • Docker & Kubernetes • Cloud Security • DevOps & DevSecOps

---

⭐ *If you find this repository helpful, please consider giving it a star on GitHub!*
